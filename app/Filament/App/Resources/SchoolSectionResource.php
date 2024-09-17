<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SchoolSectionResource\Pages;
use App\Filament\App\Resources\SchoolSectionResource\RelationManagers;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SchoolSectionResource extends Resource
{
    protected static ?string $model = SchoolSection::class;
    protected static ?string $navigationGroup = 'Academic';

    protected static ?string $label = 'Section';

    protected static ?string $navigationIcon = 'heroicon-s-cog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\TextInput::make('section')
                ->label('Section')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('category')
                ->label('Category')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('capacity')
                ->label('Capacity')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('class_id')
                ->label('Class Name')
                ->options(SchoolClass::all()->pluck('name', 'id'))
                ->searchable(),
            Forms\Components\Select::make('teacher_id')
                ->label('Teacher Name')
                ->options(Teacher::all()->pluck('name', 'id'))
                ->searchable(),
            Forms\Components\Textarea::make('note')
                ->label('Notes')
                ->required()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('section')
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
            'index' => Pages\ListSchoolSections::route('/'),
            'create' => Pages\CreateSchoolSection::route('/create'),
            'view' => Pages\ViewSchoolSection::route('/{record}'),
            'edit' => Pages\EditSchoolSection::route('/{record}/edit'),
        ];
    }
}
