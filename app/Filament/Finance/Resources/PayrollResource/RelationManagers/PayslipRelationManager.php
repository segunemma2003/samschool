<?php

namespace App\Filament\Finance\Resources\PayrollResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PayslipRelationManager extends RelationManager
{
    protected static string $relationship = 'payslip';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('payslip_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('issue_date')
                    ->required(),
                Forms\Components\FileUpload::make('file_path')
                    ->directory('payslips')
                    ->visibility('private')
                    ->disk('s3')
                    ->preserveFilenames()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('payslip_number')
            ->columns([
                Tables\Columns\TextColumn::make('payslip_number'),
                Tables\Columns\TextColumn::make('issue_date')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
