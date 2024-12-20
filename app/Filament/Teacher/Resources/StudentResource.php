<?php

namespace App\Filament\Teacher\Resources;

use App\Exports\StudentExport;
use App\Filament\Teacher\Resources\StudentResource\Pages;
use App\Filament\Teacher\Resources\StudentResource\Pages\CourseFormStudent;
use App\Filament\Teacher\Resources\StudentResource\Pages\StudentResultDetailsPage;
use App\Filament\Teacher\Resources\StudentResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Arm;
use App\Models\CourseForm;
use App\Models\Guardians;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Knp\Snappy\Pdf as KnpSnappyPdf;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Spatie\LaravelPdf\Facades\Pdf as FacadesPdf;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->disk('cloudinary')
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
                        $query->whereIn('class_id', $classIds);
                    } else {
                        // Return an empty result if no classes are associated with the teacher
                        $query->whereRaw('1 = 0'); // Always false condition
                    }
                } else {
                    // Return an empty result if no teacher is found
                    $query->whereRaw('1 = 0'); // Always false condition
                }
            } else {
                // Return an empty result if no authenticated user
                $query->whereRaw('1 = 0'); // Always false condition
            }
        })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
                Tables\Columns\TextColumn::make('username')
               ->searchable(),
                Tables\Columns\TextColumn::make('email')
                ->searchable(),
                Tables\Columns\TextColumn::make('class.name')
                ->searchable(),
            ])
            ->filters([
                SelectFilter::make('class')
                ->relationship('class', 'name')
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

                \Filament\Tables\Actions\Action::make('downloadSingleResult')
                ->label('Download Result')
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
                ])
                ->action(function (array $data, $record) {
                    $student = $record;

                    // Batch load related models
                    $academy = AcademicYear::find($data['academic_id']);
                    $term = Term::find($data['term_id']);

                    $school = SchoolInformation::where([
                        ['term_id', $term->id],
                        ['academic_id', $academy->id]
                    ])->first();

                    $studentAttendance = StudentAttendanceSummary::where([
                        ['term_id', $term->id],
                        ['academic_id', $academy->id]
                    ])->first();

                    $studentComment = StudentComment::where([
                        ['student_id', $student->id],
                        ['term_id', $term->id],
                        ['academic_id', $academy->id]
                    ])->first();

                    // Eager load related data for courses
                    $courses = CourseForm::where([
                            ['student_id', $student->id],
                            ['academic_year_id', $academy->id],
                            ['term_id', $term->id]
                        ])
                        ->get();

                    // Optimize headings query with eager loading
                    $headings = ResultSectionType::with('resultSection')
                        ->whereHas('resultSection', function ($query) use ($student) {
                            $query->where('group_id', $student->class->group->id);
                        })
                        ->get();

                    // Group headings by patterns
                    $markObtained = $headings->whereIn('calc_pattern', ['input', 'total']) ?? collect([]);
                    $studentSummary = $headings->whereIn('calc_pattern', ['position', 'grade_level']) ?? collect([]);
                    $termSummary = $headings->whereIn('calc_pattern', ['class_average', 'class_highest_score', 'class_lowest_score']) ?? collect([]);
                    $remarks = $headings->whereIn('calc_pattern', ['remarks']) ?? collect([]);

                    $class = SchoolClass::where('id', $student->class->id)->first() ?? collect([]);

                    $data = [
                        'class'=>$class,
                        'markObtained'=>$markObtained,
                        'remarks'=>$remarks,
                        'studentSummary'=> $studentSummary,
                        'termSummary'=>$termSummary,
                        'courses'=>$courses,
                        'studentComment'=>$studentComment,
                        'student'=>$student,
                         'school'=>$school,
                         'academy'=>$academy,
                          'studentAttendance'=>$studentAttendance,
                          'term'=>$term
                    ];
                    // dd($data);

                    $time = time();
                    // $html = view('results.template', $data)->render();

                    $pdf = SnappyPdf::loadView('results.template', $data);
                    return response()->streamDownload(
                            fn () => print($pdf->output()),
                            "result-{$record->name}.pdf"
                        );
                    // return $pdf->download('invoice.pdf');
                    // $pdf = PDF::loadView('results.template', $data);
                    // return response()->streamDownload(function () use ($pdf) {
                    //     echo $pdf->stream();
                    //     }, "result-{$student->name}-".time().'.pdf');
                    // return $pdf->download("result-{$student->name}-".time().'.pdf');
                    // $html = view('results.template', $data)->render();
                    // dd($html);

                    // // // Initialize mPDF
                    // $mpdf = new Mpdf();

                    // // // Load the HTML content
                    // $mpdf->WriteHTML($html);
                    // dd($mpdf);
                    // // Output the PDF as a download
                    // return response($mpdf->Output("result-{$record->name}-$time.pdf", 'D'))
                    //     ->header('Content-Type', 'application/pdf');
//                     $time = time();
//                     $pdf = FacadesPdf::view('results.template',$data)->format('a4')->disk('cloudinary')->save("result-{$record->name}-$time.pdf");

//                     $url = Storage::disk('cloudinary')->url("result-{$record->name}-$time.pdf");

// // Redirect to the file for download
// return response()->streamDownload(function () use ($url) {
//     echo file_get_contents($url);
// }, "result-{$record->name}.pdf");
                    // $pdf = Pdf::loadView('results.template',compact('class','markObtained','remarks','studentSummary','termSummary','courses','studentComment','student', 'school', 'academy', 'studentAttendance', 'term'))->setPaper('a4', 'portrait');
                    // return response()->streamDownload(
                    //     fn () => print($pdf->output()),
                    //     "result-{$record->name}.pdf"
                    // );
                }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
