<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\SubjectResource\Pages;
use App\Filament\Teacher\Resources\SubjectResource\RelationManagers;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectDepot;
use App\Models\Teacher;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open'; // More relevant icon for subjects
    protected static ?string $navigationGroup = 'Academic Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 Select::make('subject_depot_id')
                    ->label('Subject')
                    ->options(function() {
                        return cache()->remember('subject_depot_options', 300, function() {
                            return SubjectDepot::all()->pluck('name', 'id');
                        });
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('class_id')
                    ->label('Class Name')
                    ->options(SchoolClass::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('teacher_id')
                    ->label('Teacher Name')
                    ->options(Teacher::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                // Forms\Components\Select::make('type')
                //     ->options([
                //         'optional' => 'Optional',
                //         'mandatory' => 'Mandatory',

                //     ])->required(),
                Forms\Components\TextInput::make('pass_mark')
                    ->integer()
                    ->required(),
                    // ->maxLength(255),
                Forms\Components\TextInput::make('final_mark')
                ->integer()
                    ->required(),
                    // ->maxLength(255),

                // Forms\Components\TextInput::make('author')
                //     // ->integer()
                //     ->required()
                //     ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label('Subject Code')
                    ->unique(table: Subject::class, ignoreRecord: true)
                    ->required()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) {
            $userId = Auth::user()->id;
            $user = User::whereId($userId)->first();
            $teacher = Teacher::where('email', $user->email)->first();
            $query->where('teacher_id', $teacher->id);
        })
            ->columns([
                Tables\Columns\TextColumn::make('subjectDepot.name')
                ->label('Subject')
                ->searchable(),
                Tables\Columns\TextColumn::make('class.name')
                ->label('Class')
                ->searchable(),
                Tables\Columns\TextColumn::make('code')
                ->label('Subject Code')
                ->searchable(),
                Tables\Columns\TextColumn::make('pass_mark')
                ->label('Pass Mark'),
                Tables\Columns\TextColumn::make('final_mark')
                ->label('Final Mark'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->striped(); // Zebra striping for readability
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
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),

        ];
    }
}
