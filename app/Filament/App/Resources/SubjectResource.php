<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SubjectResource\Pages;
use App\Filament\App\Resources\SubjectResource\RelationManagers;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectDepot;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationGroup = 'Academic';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            // Forms\Components\TextInput::make('name')
            //     ->required()
            //     ->maxLength(255),

            Select::make('subject_depot_id')
            ->label('Subject')
            ->options(SubjectDepot::all()->pluck('name', 'id'))
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
            ->columns([
                Tables\Columns\TextColumn::make('subjectDepot.name')
                ->searchable(),
                Tables\Columns\TextColumn::make('class.name')
                ->searchable(),
                Tables\Columns\TextColumn::make('code')
                ->searchable(),
            ])
            ->filters([
                //
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
            'view' => Pages\ViewSubject::route('/{record}'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
