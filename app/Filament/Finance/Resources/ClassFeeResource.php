<?php

namespace App\Filament\Finance\Resources;

use App\Filament\Finance\Resources\ClassFeeResource\Pages;
use App\Filament\Finance\Resources\ClassFeeResource\RelationManagers;
use App\Models\ClassFee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassFeeResource extends Resource
{
    protected static ?string $model = ClassFee::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Class Fee Assignment')
                    ->schema([
                        Forms\Components\Select::make('school_class_id')
                            ->relationship('schoolClass', 'name')
                            ->required(),
                        Forms\Components\Select::make('fee_structure_id')
                            ->relationship('feeStructure', 'name')
                            ->required(),
                        Forms\Components\Select::make('academic_year_id')
                            ->relationship('academicYear', 'title')
                            ->required(),
                        Forms\Components\Select::make('term_id')
                            ->relationship('term', 'name')
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('schoolClass.name')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('feeStructure.name')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('academicYear.title')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('term.name')
                ->numeric()
                ->sortable(),
            Tables\Columns\IconColumn::make('is_active')
                ->boolean(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_class_id')
                ->relationship('schoolClass', 'name'),
            Tables\Filters\SelectFilter::make('academic_year_id')
                ->relationship('academicYear', 'title'),
            Tables\Filters\SelectFilter::make('term_id')
                ->relationship('term', 'name'),
            Tables\Filters\TernaryFilter::make('is_active')
                ->label('Active')
                ->boolean()
                ->trueLabel('Only Active')
                ->falseLabel('Only Inactive')
                ->native(false),
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
            RelationManagers\StudentFeesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassFees::route('/'),
            'create' => Pages\CreateClassFee::route('/create'),
            'view' => Pages\ViewClassFee::route('/{record}'),
            'edit' => Pages\EditClassFee::route('/{record}/edit'),
        ];
    }
}
