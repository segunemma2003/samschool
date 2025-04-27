<?php

namespace App\Filament\Finance\Resources;

use App\Filament\Finance\Resources\FundRaisingProgramResource\Pages;
use App\Filament\Finance\Resources\FundRaisingProgramResource\RelationManagers;
use App\Models\FundRaisingProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FundRaisingProgramResource extends Resource
{
    protected static ?string $model = FundRaisingProgram::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Program Information')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('target_amount')
                        ->required()
                        ->numeric()
                        ->prefix('₦'),
                    Forms\Components\DatePicker::make('start_date')
                        ->required(),
                    Forms\Components\DatePicker::make('end_date')
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
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('target_amount')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_raised')
                    ->money('NGN'),
                Tables\Columns\TextColumn::make('progress')
                    ->formatStateUsing(function (FundraisingProgram $record) {
                        $percentage = ($record->amount_raised / $record->target_amount) * 100;
                        return number_format($percentage, 2) . '%';
                    })
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
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
            RelationManagers\ContributionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFundRaisingPrograms::route('/'),
            'create' => Pages\CreateFundRaisingProgram::route('/create'),
            'view' => Pages\ViewFundRaisingProgram::route('/{record}'),
            'edit' => Pages\EditFundRaisingProgram::route('/{record}/edit'),
        ];
    }
}
