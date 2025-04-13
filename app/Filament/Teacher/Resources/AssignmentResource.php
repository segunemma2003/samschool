<?php

namespace App\Filament\Teacher\Resources;

// use App\Filament\Clusters\Assignment as ClustersAssignment;
use App\Filament\Teacher\Clusters\Assignment as TeacherClustersAssignment;
use App\Filament\Teacher\Pages\SubmittedStudentsList;
use App\Filament\Teacher\Resources\AssignmentResource\Pages;
use App\Filament\Teacher\Resources\AssignmentResource\Pages\SubmittedStudents;
use App\Filament\Teacher\Resources\AssignmentResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;
    // protected static ?string $cluster = TeacherClustersAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    public static function form(Form $form): Form
    {
        $id = Auth::user()->id;
        $user = User::whereId($id)->first();
        $teacherId = Teacher::whereEmail($user->email)->first();
        return $form
            ->schema([
                TextInput::make('title')->required(),
                DatePicker::make('deadline')->required(),
                TextInput::make('weight_mark')
                ->label('Total Mark')
                ->numeric()
                ->required(),
                Select::make('class_id')
                ->label('Class Name')
                ->options(SchoolClass::where('teacher_id',$teacherId->id)->pluck('name', 'id'))
                ->searchable(),
                Forms\Components\Select::make('term_id')
                    ->label('Term')
                    ->options(Term::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('academic_id')
                    ->label('Academy')
                    ->options(AcademicYear::all()->pluck('title', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('subject_id')
                    ->label('Subject')
                    ->options(Subject::where('teacher_id',$teacherId->id)->pluck('code', 'id'))
                    ->searchable(),
                FileUpload::make('file')->disk('s3'),
                RichEditor::make('description')->required()
                ->label("Question")
                ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) {
            $userId = Auth::user()->id;
            $user = User::whereId($userId)->first();
            $teacherId = Teacher::whereEmail($user->email)->first();
            $query->where('teacher_id', $teacherId->id);
        })
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('class.name'),
                Tables\Columns\TextColumn::make('term.name')
                ->searchable(),

                Tables\Columns\TextColumn::make('academy.title')
                ->searchable(),
                TextColumn::make('total_students_answered')->label('Total Students Submitted'),
                TextColumn::make('deadline'),
                TextColumn::make('created_at')->since()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                // Action::make('view')->Label('view submissions')
                // ->url(fn ($record): string => route('filament.pages.submitted-students-list', ['assignment'=>$record]))


            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\ViewAction::make()
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
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
            'view'=> SubmittedStudents::route('/{record}/assignments')
        ];
    }
}
