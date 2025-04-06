<?php

namespace App\Filament\Ourparent\Resources;

use App\Filament\Ourparent\Resources\StudentResource\Pages;
use App\Filament\Ourparent\Resources\StudentResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\CourseForm;
use App\Models\Guardians;
use App\Models\PsychomotorCategory;
use App\Models\ResultSectionType;
use App\Models\SchoolClass;
use App\Models\SchoolInformation;
use App\Models\Student;
use App\Models\StudentAttendanceSummary;
use App\Models\StudentComment;
use App\Models\Term;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Google\Service\Classroom\Guardian;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) {
            $userId = Auth::user()->email;
            $guardian = Guardians::whereEmail($userId)->first();
            // dd($guardian);
            $query->where('guardian_id',$guardian->id);

        })
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('class.name'),
                TextColumn::make('gender'),
                TextColumn::make('religion')
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('downloadSingleResult')
                ->label('Download Result')
                ->icon('heroicon-s-arrow-down-on-square')
                ->form([
                    Forms\Components\Select::make('term_id')
                        ->options(Term::whereNotNull('name')->pluck('name', 'id'))
                        ->preload()
                        ->label('Term')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('academic_id')
                        ->label('Academy Year')
                        ->options(AcademicYear::whereNotNull('title')->pluck('title', 'id'))
                        ->preload()
                        ->required()
                        ->searchable(),
                    Forms\Components\Select::make('mid')
                        ->label('Mid Term Result?')
                        ->options([
                            "Yes" => "Yes",
                            "No" => "No"
                        ])
                        ->required()
                        ->preload()
                        ->searchable(),
                ])
                ->action(function (array $data, $record) {
                    // Build the URL dynamically
                    $url = route('student.result.check', [
                        'studentId' => $record->id,
                        'termId' => $data['term_id'],
                        'academyId' => $data['academic_id'],
                    ]);

                    // Redirect to the generated URL
                    return redirect($url);
                }),
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

                //     // Step 3: Iterate through the courses and calculate the total score
                //     // foreach ($courses as $course) {
                //     //     foreach ($totalHeadings as $heading) {
                //     //         $subject = $course->subject->subjectDepot->name;
                //     //         $score = $course->scoreBoard->firstWhere('result_section_type_id', $heading->id);
                //     //         if(strtolower($subject) == 'english'){
                //     //             $englishScore = $score->score ?? 0;
                //     //         }
                //     //         if(strtolower($subject) == 'maths' || (strtolower($subject) == 'mathematics')){
                //     //             $mathScore = $score->score ?? 0;
                //     //         }

                //     //         // Retrieve the score for this subject and heading


                //     //         // Add the score to the total (only if it exists)
                //     //         $totalScore += $score->score ?? 0;
                //     //     }
                //     // }
                //     // dd($totalScore);
                //     // dd($totalSubject);

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

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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



    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
