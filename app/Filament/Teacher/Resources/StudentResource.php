<?php

namespace App\Filament\Teacher\Resources;

use App\Exports\StudentExport;
use App\Filament\Teacher\Resources\StudentResource\Pages;
use App\Filament\Teacher\Resources\StudentResource\Pages\CourseFormStudent;
use App\Filament\Teacher\Resources\StudentResource\Pages\StudentResultDetailsPage;
use App\Filament\Teacher\Resources\StudentResource\RelationManagers;
use App\Jobs\GenerateBroadSheet;
use App\Jobs\GenerateStudentResultPdf;
use App\Models\AcademicYear;
use App\Models\Arm;
use App\Models\CourseForm;
use App\Models\DownloadStatus;
use App\Models\Guardians;
use App\Models\PsychomotorCategory;
use App\Models\StudentResult;
use App\Models\ResultSectionType;
use App\Models\SchoolClass;
use App\Models\SchoolInformation;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\StudentAttendanceSummary;
use App\Models\StudentComment;
use App\Models\StudentGroup;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Exception;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Knp\Snappy\Pdf as KnpSnappyPdf;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use phpDocumentor\Reflection\PseudoTypes\LowercaseString;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf as FacadesPdf;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Academic Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('registration_number')
                ->label('Admission Number')
                ->unique(ignoreRecord: true)
                    ->disabled(fn(Student $student) => $student->exists)
                    ->default(fn() => 'STD-' . random_int(100000000, 999999999))
                    ->required()
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                // Forms\Components\TextInput::make('email')
                //     ->email()
                //     ->unique(table: Student::class)
                //     // ->default()
                //     ->required()
                //     ->maxLength(255),
                // Forms\Components\DatePicker::make('date_of_birth')
                //     ->required()
                //    ,
                Forms\Components\Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])->required(),
                Forms\Components\TextInput::make('blood_group')
                    ->maxLength(255),
                Forms\Components\TextInput::make('height')
                ->maxLength(255),
                Forms\Components\TextInput::make('weight')
                ->maxLength(255),
                Forms\Components\Select::make('religion')
                    ->options([
                        'christianity' => 'Christianity',
                        'islam' => 'Islam',
                        'others' => 'Others',
                    ])->required(),
                // Forms\Components\DatePicker::make('joining_date')->required(),
                Forms\Components\DatePicker::make('date_of_birth'),
                Forms\Components\TextInput::make('phone')
                    ->label('Phone number')
                    ->tel()
                    ,
                Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('state')
                    ->required()
                    ->label('State of Origin')
                    ->maxLength(255),
                // Forms\Components\TextInput::make('country')
                //     ->required()
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('username')->unique(table: Student::class)
                //             ->maxLength(255)->required(),
                // Forms\Components\TextInput::make('optional_subject')
                //             ->required()
                //             ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                // Forms\Components\TextInput::make('roll')
                //     ->required()
                //     ->maxLength(255),
                Forms\Components\Textarea::make('remarks')
                ->label('Medical/Allergies')
                    // ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('class_id')
                    ->label('Class Name')
                    ->options(SchoolClass::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('guardian_id')
                    ->label('Guardian')
                    ->options(Guardians::all()->pluck('name', 'id'))
                    ->searchable(),
                // Forms\Components\Select::make('section_id')
                //     ->label('Section')
                //     ->options(SchoolSection::all()->pluck('section', 'id'))
                //     ->searchable(),
                Forms\Components\Select::make('arm_id')
                    ->label('Arms')
                    ->options(Arm::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('group_id')
                    ->label('Group')
                    ->options(StudentGroup::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\FileUpload::make('avatar')
                ->label('Passport')
                    ->disk('s3')
                        ->required(),
                Forms\Components\Select::make('user_type')
                        ->options([
                            'teacher' => 'teacher',
                            'student' => 'student',
                            'parent' => 'parent',
                            'admin'=>'admin'
                        ])->default('student'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                if ($user) {
                    $teacher = Teacher::where('email', $user->email)->first();
                    if ($teacher) {
                        $classIds = $teacher->classes()->pluck('id');
                        if ($classIds->isNotEmpty()) {
                            // Add eager loading here
                            $query->with(['class', 'arm', 'guardian'])
                                  ->whereIn('class_id', $classIds);
                        } else {
                            $query->whereRaw('1 = 0');
                        }
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
                Tables\Columns\TextColumn::make('username')
               ->searchable(),
                Tables\Columns\TextColumn::make('email')
                ->searchable(),
                Tables\Columns\TextColumn::make('arm.name')
                ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('class.name')
                ->searchable()->sortable(),
            ])
            ->filters([
                SelectFilter::make('class')
                ->relationship('class', 'name')
                ->searchable()
                ->preload()
                ,
                SelectFilter::make('arm')
                ->relationship('arm', 'name')
                ->searchable()
                ->preload()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\Action::make('view')
                ->label('View CourseForm')
                ->url(fn ($record) => CourseFormStudent::generateRoute($record->id)),


                \Filament\Tables\Actions\Action::make('viewresult')
                ->label('View Result')
                ->url(fn ($record) => StudentResultDetailsPage::generateRoute($record->id)),

                \Filament\Tables\Actions\Action::make('previewResult')
                    ->label('Preview Result')
                    ->icon('heroicon-s-eye')
                    ->color('info')
                    ->form([
                        \Filament\Forms\Components\Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->options(\App\Models\AcademicYear::all()->pluck('title', 'id'))
                            ->required()
                            ->searchable(),
                        \Filament\Forms\Components\Select::make('term_id')
                            ->label('Term')
                            ->options(\App\Models\Term::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            // Check if student has course forms for this term and academic year
                            $courseForms = \App\Models\CourseForm::where('student_id', $record->id)
                                ->where('term_id', $data['term_id'])
                                ->where('academic_year_id', $data['academic_year_id'])
                                ->get();

                            if ($courseForms->isEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('No Course Forms Available')
                                    ->body('No course forms found for this student in the selected term and academic year.')
                                    ->send();
                                return;
                            }

                            // Generate preview URL
                            $previewUrl = route('student.result.preview', [
                                'studentId' => $record->id,
                                'termId' => $data['term_id'],
                                'academicYearId' => $data['academic_year_id']
                            ]);

                            // Redirect to preview URL
                            return redirect()->away($previewUrl);

                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error Generating Preview')
                                ->body('Failed to generate result preview: ' . $e->getMessage())
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Preview Student Result')
                    ->modalDescription('Select the academic year and term to preview the result.')
                    ->modalSubmitActionLabel('Preview Result')
                    ->modalCancelActionLabel('Cancel'),

                \Filament\Tables\Actions\Action::make('quickDownloadResult')
                    ->label('Quick Download')
                    ->icon('heroicon-s-arrow-down-tray')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->options(\App\Models\AcademicYear::all()->pluck('title', 'id'))
                            ->required()
                            ->searchable(),
                        \Filament\Forms\Components\Select::make('term_id')
                            ->label('Term')
                            ->options(\App\Models\Term::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            // Check if student has course forms for this term and academic year
                            $courseForms = \App\Models\CourseForm::where('student_id', $record->id)
                                ->where('term_id', $data['term_id'])
                                ->where('academic_year_id', $data['academic_year_id'])
                                ->get();

                            if ($courseForms->isEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('No Course Forms Available')
                                    ->body('No course forms found for this student in the selected term and academic year.')
                                    ->send();
                                return;
                            }

                            // Generate download URL
                            $downloadUrl = route('student.result.download', [
                                'studentId' => $record->id,
                                'termId' => $data['term_id'],
                                'academicYearId' => $data['academic_year_id']
                            ]);

                            // Log the URL for debugging
                            Log::info('Download URL generated', [
                                'url' => $downloadUrl,
                                'student_id' => $record->id,
                                'term_id' => $data['term_id'],
                                'academic_year_id' => $data['academic_year_id']
                            ]);

                            // Redirect to download URL
                            return redirect()->away($downloadUrl);

                        } catch (\Exception $e) {
                            Log::error('Download action error', [
                                'error' => $e->getMessage(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine(),
                                'trace' => $e->getTraceAsString()
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error Generating Download')
                                ->body('Failed to generate result download: ' . $e->getMessage())
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Download Student Result')
                    ->modalDescription('Select the academic year and term to download the result.')
                    ->modalSubmitActionLabel('Download PDF')
                    ->modalCancelActionLabel('Cancel'),

                // \Filament\Tables\Actions\Action::make('downloadSingleResult')
                // ->label('Download Result')
                // ->icon('heroicon-s-arrow-down-on-square')
                // ->form([
                //     Forms\Components\Select::make('term_id')
                //         ->options(Term::all()->pluck('name', 'id'))
                //         ->preload()
                //         ->label('Term')
                //         ->searchable()
                //         ->required(),
                //     Forms\Components\Select::make('academic_id')
                //     ->label('Academy Year')
                //     ->options(AcademicYear::all()->pluck('title', 'id'))
                //     ->preload()
                //     ->searchable(),
                // ])
                // ->action(function (array $data, $record) {
                //     $student = $record;

                //     // Batch load related models
                //     $academy = AcademicYear::find($data['academic_id']);
                //     $term = Term::find($data['term_id']);

                //     $school = SchoolInformation::where([
                //         ['term_id', $term->id],
                //         ['academic_id', $academy->id]
                //     ])->first();

                //     $studentAttendance = StudentAttendanceSummary::where([
                //         ['term_id', $term->id],
                //         ['student_id', $student->id],
                //         ['academic_id', $academy->id]
                //     ])->first();

                //     $studentComment = StudentComment::where([
                //         ['student_id', $student->id],
                //         ['term_id', $term->id],
                //         ['academic_id', $academy->id]
                //     ])->first();

                //     // Eager load related data for courses
                //     $courses = CourseForm::with([
                //         'subject.subjectDepot',
                //         'scoreBoard'
                //     ])->where([
                //             ['student_id', $student->id],
                //             ['academic_year_id', $academy->id],
                //             ['term_id', $term->id]
                //         ])
                //         ->get();

                //     // Optimize headings query with eager loading
                //     $headings = ResultSectionType::with('resultSection')
                //         ->whereHas('resultSection', function ($query) use ($student) {
                //             $query->where('group_id', $student->class->group->id);
                //         })
                //         ->get();

                //     $psychomotorCategory = PsychomotorCategory::all();

                //     // Group headings by patterns
                //     $markObtained = $headings->whereIn('calc_pattern', ['input', 'total']) ?? collect([]);
                //     $studentSummary = $headings->whereIn('calc_pattern', ['position', 'grade_level']) ?? collect([]);
                //     $termSummary = $headings->whereIn('calc_pattern', ['class_average', 'class_highest_score', 'class_lowest_score']) ?? collect([]);
                //     $remarks = $headings->whereIn('calc_pattern', ['remarks']) ?? collect([]);

                //     $class = SchoolClass::where('id', $student->class->id)->first() ?? collect([]);
                //     $totalSubject =count($courses);

                //     $totalHeadings = $headings->where('calc_pattern', 'total');

                //     // Step 2: Initialize a variable to store the total sum
                //     $totalScore = 0;
                //     $englishScore = 0;
                //     $mathScore = 0;
                //     $totalScore = $courses->reduce(function ($carry, $course) use ($totalHeadings, &$englishScore, &$mathScore) {
                //         foreach ($totalHeadings as $heading) {
                //             $score = $course->scoreBoard->firstWhere('result_section_type_id', $heading->id);
                //             $scoreValue = $score->score ?? 0;

                //             // Add to the total score
                //             $carry += $scoreValue;

                //             // Check if the subject is English or Maths and store their scores
                //             $subject = $course->subject->subjectDepot->name;
                //             if ((strncasecmp($subject, 'english', 7) === 0) || (strncasecmp($subject, 'literacy', 8) === 0)){
                //                 $englishScore = $scoreValue;
                //             } elseif ((strncasecmp($subject,'math', 4)  == 0)|| (strncasecmp($subject, 'numeracy', 8) === 0)) {
                //                 $mathScore = $scoreValue;
                //             }
                //         }
                //         return $carry;
                //     }, 0);


                //     $percent = round(($totalScore / $totalSubject));
                //     // dd($percent);

                //     $principalComment = self::getPerformanceComment($percent, $englishScore, $mathScore);

                //     $data = [
                //         'class'=>$class,
                //         'totalSubject'=>$totalSubject,
                //         'totalScore'=>$totalScore,
                //         'percent'=>$percent,
                //         'markObtained'=>$markObtained,
                //         'remarks'=>$remarks,
                //         'studentSummary'=> $studentSummary,
                //         'termSummary'=>$termSummary,
                //         'courses'=>$courses,
                //         'studentComment'=>$studentComment,
                //         'student'=>$student,
                //         'school'=>$school,
                //         'academy'=>$academy,
                //         'studentAttendance'=>$studentAttendance,
                //         'term'=>$term,
                //         'principalComment' => $principalComment,
                //         'psychomotorCategory'=> $psychomotorCategory
                //     ];


                //     $time = time();
                //     // $html = view('results.template', $data)->render();

                //     $pdf = SnappyPdf::loadView('results.template', $data);
                //     return response()->streamDownload(
                //             fn () => print($pdf->output()),
                //             "result-{$record->name}.pdf"
                //         );
                //     // $pdf = Pdf::loadView('results.template',compact('psychomotorCategory','class','markObtained','remarks','studentSummary','termSummary','courses','studentComment','student', 'school', 'academy', 'studentAttendance', 'term', 'totalScore','totalSubject', 'percent','principalComment'))->setPaper('a4', 'portrait');
                //     // return response()->streamDownload(
                //     //     fn () => print($pdf->output()),
                //     //     "result-{$record->name}.pdf"
                //     // );
                // }),
                Tables\Actions\Action::make('downloadSingleResult')
    ->label('Download Result')
    ->icon('heroicon-s-arrow-down-on-square')
    ->color('success')
    ->form([
        Forms\Components\Select::make('term_id')
            ->options(Term::whereNotNull('name')->pluck('name', 'id'))
            ->preload()
            ->label('Term')
            ->searchable()
            ->required(),
        Forms\Components\Select::make('academic_id')
            ->label('Academic Year')
            ->options(AcademicYear::whereNotNull('title')->pluck('title', 'id'))
            ->preload()
            ->required()
            ->searchable(),
    ])
    ->action(function (array $data, $record) {
        try {


            // Check if results exist for this student
            $resultsExist = StudentResult::where([
                ['student_id', $record->id],
                ['term_id', $data['term_id']],
                ['academic_year_id', $data['academic_id']]
            ])->exists();

            if (!$resultsExist) {
                Notification::make()
                    ->title('Result Not Ready Yet')
                    ->body('No saved results found for this student in the selected term and academic year. Please ensure the teacher has viewed and commented on the student result first.')
                    ->warning()
                    ->duration(8000)
                    ->send();
                return;
            }

            // Use the updated service that works with saved results
            $resultService = new \App\Services\ResultPdfService();
            $result = $resultService->generateStudentResult(
                $record->id,
                $data['term_id'],
                $data['academic_id']
            );

            Notification::make()
                ->title('PDF Generated Successfully')
                ->body('Student result has been generated and will download automatically.')
                ->success()
                ->send();

            return response()->streamDownload(
                fn () => print($result['pdf']->output()),
                $result['filename'],
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $result['filename'] . '"'
                ]
            );

        } catch (\Exception $e) {
            Log::error('PDF Download Error', [
                'student_id' => $record->id,
                'term_id' => $data['term_id'] ?? null,
                'academic_id' => $data['academic_id'] ?? null,
                'error' => $e->getMessage()
            ]);

            Notification::make()
                ->title('Error Generating PDF')
                ->body('Failed to generate PDF: ' . $e->getMessage())
                ->danger()
                ->duration(10000)
                ->send();

            return;
        }
    })
    ->requiresConfirmation()
    ->modalHeading('Download Student Result')
    ->modalDescription('This will generate a PDF report card using the saved results data. Make sure results have been entered and commented on before downloading.')
    ->modalSubmitActionLabel('Generate & Download PDF')
    ->modalCancelActionLabel('Cancel'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('bulkDownloadResults')
                        ->label('Download Results (Saved)')
                        ->icon('heroicon-s-arrow-down-on-square')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('term_id')
                                ->options(Term::all()->pluck('name', 'id'))
                                ->preload()
                                ->label('Term')
                                ->searchable()
                                ->required(),
                            Forms\Components\Select::make('academic_id')
                                ->label('Academic Year')
                                ->options(AcademicYear::all()->pluck('title', 'id'))
                                ->preload()
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            try {
                                $studentsWithResults = [];
                                $studentsWithoutResults = [];

                                // Check which students have results
                                foreach ($records as $student) {
                                    $hasResults = StudentResult::where([
                                        ['student_id', $student->id],
                                        ['term_id', $data['term_id']],
                                        ['academic_year_id', $data['academic_id']]
                                    ])->exists();

                                    if ($hasResults) {
                                        $studentsWithResults[] = $student;
                                    } else {
                                        $studentsWithoutResults[] = $student->name;
                                    }
                                }

                                if (empty($studentsWithResults)) {
                                    Notification::make()
                                        ->title('Results Not Ready Yet')
                                        ->body('None of the selected students have saved results for the selected term and academic year. Please ensure teachers have viewed and commented on the student results first.')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                // Queue the bulk generation job
                                $status = DownloadStatus::create([
                                    'status' => 'processing',
                                    'time' => time(),
                                    'data' => json_encode([
                                        'term_id' => $data['term_id'],
                                        'academic_id' => $data['academic_id'],
                                        'student_count' => count($studentsWithResults),
                                        'students_without_results' => $studentsWithoutResults
                                    ])
                                ]);

                                // Dispatch your existing job but modify it to use saved results
                                GenerateStudentResultPdf::dispatch($data, collect($studentsWithResults), $status->id);

                                $message = 'Bulk download is processing in the background for ' . count($studentsWithResults) . ' students.';
                                if (!empty($studentsWithoutResults)) {
                                    $message .= ' Note: ' . count($studentsWithoutResults) . ' students were skipped due to missing results.';
                                }

                                Notification::make()
                                    ->title('Bulk Download Started')
                                    ->body($message)
                                    ->success()
                                    ->duration(8000)
                                    ->send();

                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error Starting Bulk Download')
                                    ->body('Failed to start bulk download: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Download Student Results')
                        ->modalDescription('This will generate PDF report cards for all selected students using their saved results data.')
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('DownloadResult')
                    ->label('Download Results')
                    ->icon('heroicon-s-arrow-down-on-square')
                    ->form([
                        Forms\Components\Select::make('term_id')
                            ->options(Term::all()->pluck('name', 'id'))
                            ->preload()
                            ->label('Term')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('academic_id')
                        ->label('Academy Year')
                        ->options(AcademicYear::all()->pluck('title', 'id'))
                        ->preload()
                        ->searchable(),

                        Forms\Components\Select::make('mid')
                            ->label('Mid Term Result?')
                            ->options([
                                "Yes"=>"Yes",
                                "No"=> "No"
                            ])
                            ->required()
                            ->preload()
                            ->searchable(),
                    ]) ->action(function (array $data, $records) {
                        // $selectedRecords = $this->getSelectedRecords();
                        // dd($data);
                        $students = $records;
                        $status = DownloadStatus::create([
                            'status'=>'processing',
                            'time'=> time(),
                            'data'=> json_encode($data)
                        ]);
                        GenerateStudentResultPdf::dispatch($data,$students, $status->id);
                        Notification::make()
                        ->title('Download is processing on the background')
                        ->success()
                        ->send();
                    })
                ]),
            ]);
    }


    public static function getPerformanceComment($percentage, $englishScore, $mathScore) {
        // Define comments based on percentage range
        $comments = [
            'excellent' => [
                "Excellent Performance",
                "Keep it up",
                "Do not relent on your efforts. Keep it up"
            ],
            'very_good' => [
                "A Very Good Performance",
                "You are doing well. Keep pushing",
                "An impressive effort!"
            ],
            'hardworking' => [
                "A Hardworking Learner",
                "Your effort is commendable",
                "Good work, but you can achieve more"
            ],
            'work_harder' => [
                "Work Harder For a better result",
                "Keep improving",
                "Better focus next term will yield results"
            ],
            'put_in_effort' => [
                "Put In More Effort",
                "Your performance can improve",
                "Stay committed to better outcomes"
            ],
            'be_serious' => [
                "Be more serious in your studies",
                "Focus more on your academic goals",
                "A stronger commitment is needed"
            ],
            'english_math_fail' => [
                "Tried but should try harder next term for a better result in English and Mathematics",
                "Put in more effort in English and Mathematics",
                "A good result but can do better in English and Mathematics"
            ],
          'math_fail' => [
                "Tried but should try harder next term for a better result in Mathematics",
                "Put in more effort in Mathematics",
                "A good result but can do better in Mathematics"
            ],
          'english_fail' => [
                "Tried but should try harder next term for a better result in English",
                "Put in more effort in English",
                "A good result but can do better in English"
            ],
            'good_result' => [
                "Good results but can be better if you put in more effort",
                "Excellent Performance. Keep it up",
                "A good result. Keep it up"
            ],
            'improve_next_term' => [
                "Work harder next term for better results",
                "There is still room for improvement next term",
                "Stay focused for better performance next term"
            ]
        ];
    if ($percentage >= 70) {
        // Check English and Mathematics scores first
        if ($englishScore < 50 && $mathScore < 50) {
                return $comments['english_math_fail'][array_rand($comments['english_math_fail'])];
        } elseif ($englishScore < 50) {
            return $comments['english_fail'][array_rand($comments['english_fail'])];
        } elseif ($mathScore < 50) {
          return $comments['math_fail'][array_rand($comments['math_fail'])];
        }
      }

        // Determine the comment based on percentage
        if ($percentage >= 86) {
            $performanceComment = $comments['excellent'][array_rand($comments['excellent'])];
        } elseif ($percentage >= 75) {
            $performanceComment = $comments['very_good'][array_rand($comments['very_good'])];
        } elseif ($percentage >= 65) {
            $performanceComment = $comments['hardworking'][array_rand($comments['hardworking'])];
        } elseif ($percentage >= 51) {
            $performanceComment = $comments['work_harder'][array_rand($comments['work_harder'])];
        } elseif ($percentage >= 41) {
            $performanceComment = $comments['put_in_effort'][array_rand($comments['put_in_effort'])];
        } else {
            $performanceComment = $comments['be_serious'][array_rand($comments['be_serious'])];
        }

        return $performanceComment;
    }



    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
            'course-form'=> Pages\CourseFormStudent::route('/{record}/course-form'),
            'view-student-result-details'=> Pages\StudentResultDetailsPage::route('/{record}/student/result')
        ];
    }
}
