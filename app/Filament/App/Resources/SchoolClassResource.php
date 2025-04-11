<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SchoolClassResource\Pages;
use App\Filament\App\Resources\SchoolClassResource\RelationManagers;
use App\Models\SchoolClass;
use App\Models\StudentGroup;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static ?string $navigationGroup = 'Academic';

    protected static ?string $label = 'Class';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->label('Class')
                ->required()
                ->maxLength(255),
                Forms\Components\TextInput::make('class_numeric')
                ->label('Class Numeric')
                ->integer()
                ->required()
                ->maxLength(255),
                Forms\Components\Select::make('teacher_id')
                ->label('Year Tutor')
                ->options(Teacher::all()->pluck('name', 'id'))
                ->searchable(),
                Forms\Components\Select::make('group_id')
                ->label('School Section')
                ->options(StudentGroup::all()->pluck('name', 'id'))
                ->searchable(),
                Forms\Components\Textarea::make('note')
                ->label('Notes')
                // ->required()
    ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
                Tables\Columns\TextColumn::make('class_numeric')
                ->searchable(),
                Tables\Columns\TextColumn::make('group.name')
                ->searchable(),
                Tables\Columns\TextColumn::make('teacher.name')
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
            'index' => Pages\ListSchoolClasses::route('/'),
            'create' => Pages\CreateSchoolClass::route('/create'),
            'view' => Pages\ViewSchoolClass::route('/{record}'),
            'edit' => Pages\EditSchoolClass::route('/{record}/edit'),
        ];
    }
}
