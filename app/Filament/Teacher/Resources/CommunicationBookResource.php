<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\CommunicationBookResource\Pages;
use App\Filament\Teacher\Resources\CommunicationBookResource\RelationManagers;
use App\Models\CommunicationBook;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CommunicationBookResource extends Resource
{
    protected static ?string $model = CommunicationBook::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::with('arm')->whereEmail($user->email)->first();
        $students = [];
        if ($teacher && $teacher->arm) {
            $arm = $teacher->arm;

            $students = Student::whereHas('class', function ($query) use ($arm) {
                $query->where('class_id', $arm->class_id);
            })->whereHas('arm', function ($query) use ($arm) {
                $query->where('arm_id', $arm->arm_id);
            })->pluck('name', 'id');
        }

        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->options($students)
                    ->label('Student Name')
                    ->required()
                    ->searchable()
                    ->preload(),
                RichEditor::make('content')
                    ->required()
                    ->fileAttachmentsDisk('s3')
                    ->columnSpanFull(),

                Forms\Components\DatePicker::make('date')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
          return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = User::whereId(Auth::id())->first();
                $teacher = Teacher::with('arm')->whereEmail($user->email)->first();

                // Add eager loading
                $query->with(['student.class', 'student.arm', 'teacher'])
                      ->where('teacher_id', $teacher->id);
            })
            ->columns([
                TextColumn::make('No')
                ->rowIndex(),
                Tables\Columns\TextColumn::make('student.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.class.name')
                    ->label('Class')
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.arm.name')
                    ->label('Arm')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('date')
                ->form([DatePicker::make('date')])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['date'],
                            fn (Builder $query, $date) => $query->whereDate('date', $date)
                        );
                }),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::with('arm')->whereEmail($user->email)->first();
        // Teachers see only their students' communication books


            return $query->where('teacher_id',$teacher->id);


    }


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Student Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('student.name')
                                    ->label('Student Name'),
                                TextEntry::make('student.class.name')
                                    ->label('Class'),
                                TextEntry::make('student.arm.name')
                                    ->label('Arm'),
                            ]),
                    ]),
                Section::make('Communication')
                    ->schema([
                        TextEntry::make('date')
                            ->date(),
                        TextEntry::make('teacher.name')
                            ->label('Teacher'),
                        TextEntry::make('content')
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommunicationBooks::route('/'),
            'create' => Pages\CreateCommunicationBook::route('/create'),
            'view' => Pages\ViewCommunicationBook::route('/{record}'),
            'edit' => Pages\EditCommunicationBook::route('/{record}/edit'),
        ];
    }
}
