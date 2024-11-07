<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\AcademicYearResource\Pages;
use App\Filament\App\Resources\AcademicYearResource\RelationManagers;
use App\Models\AcademicYear;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AcademicYearResource extends Resource
{
    protected static ?string $model = AcademicYear::class;
    protected static ?string $navigationGroup = 'Academic';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),
                Forms\Components\TextInput::make('year'
                ) ->integer()
                ->required(),
                Select::make('status')
                ->options([
                    'false'=>'False',
                    'true'=>'True'
                ])->default('true'),
                Forms\Components\DatePicker::make('starting_date')->required(),
                Forms\Components\DatePicker::make('ending_date')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                ->searchable(),
                Tables\Columns\TextColumn::make('year')
                ->searchable(),
                IconColumn::make('status')
                ->color(fn (string $state): string => match ($state) {
                    'false' => 'info',

                    'true' => 'success',
                    default => 'gray',
                })->size(IconColumn\IconColumnSize::Medium),
                Tables\Columns\TextColumn::make('starting_date')
                ->searchable(),
                Tables\Columns\TextColumn::make('ending_date')
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
            'index' => Pages\ListAcademicYears::route('/'),
            'create' => Pages\CreateAcademicYear::route('/create'),
            'view' => Pages\ViewAcademicYear::route('/{record}'),
            'edit' => Pages\EditAcademicYear::route('/{record}/edit'),
        ];
    }
}
