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
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\StudentComment;
use App\Models\StudentGroup;
use App\Models\Term;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;

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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\Action::make('view')
                ->label('View CourseForm')
                ->url(fn ($record) => CourseFormStudent::generateRoute($record->id)),


                Tables\Actions\Action::make('viewresult')
    ->label('View Result')
    ->url(fn ($record) => StudentResultDetailsPage::generateRoute($record->id)),

    //             Tables\Actions\Action::make('addComments')
    // ->label('Add Comments')

    // ->steps([
    //     Step::make('Academic Year')
    //         ->description('Academic Year of the Student')
    //         ->schema([
    //             Forms\Components\Select::make('academic_year_id')
    //                 ->label('Academic Year')
    //                 ->options(AcademicYear::all()->pluck('title', 'id'))
    //                 ->required(),
    //             Forms\Components\Select::make('term_id')
    //                 ->label('Term')
    //                 ->options(Term::all()->pluck('name', 'id'))
    //                 ->required(),
    //         ])
    //         ->columns(2),
    //             Step::make('Student Details')
    //                 ->description('Details Of Student Score Board')
    //                 // ->modalContent(view('livewire.teaacher.student-result-details'))
    //                 ->schema([
    //                     ViewField::make('livewireComponent')
    //                     ->view('livewire.teaacher.custom')
    //                     ->label('Student Details')
    //                     ->viewData([ // Pass additional data to the view
    //                         'record' => $record, // Pass the current record
    //                         'academic_year_id' => $this->getState('academic_year_id'), // Pass form state for academic_year_id
    //                         'term_id' => $this->getState('term_id'), // Pass form state for term_id
    //                     ]),



    //                     Forms\Components\Textarea::make('comment')
    //                     ->label('Teacher Comment')
    //                     ->placeholder('Enter your comment here...')
    //                     ->required(),
    //             //         Forms\Components\Field::make('livewireComponent')
    //             //     ->view('livewire.teaacher.student-result-details')
    //             //      ->statePath('state')
    //             //     ->label('Student Details'),
    //             //         Forms\Components\Textarea::make('comment')
    //             //             ->label('Teacher Comment')
    //             //             ->placeholder('Enter your comment here...')
    //             //             ->required(),
    //                 ]),
    //         ])
    //         ->action(function (array $data, $record): void {
    //             // Handle form submission here
    //         })


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
