<?php

namespace App\Filament\Finance\Resources\FundRaisingProgramResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContributionsRelationManager extends RelationManager
{
    protected static string $relationship = 'contributions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('parent_id')
                    ->relationship('parent', 'name')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('₦'),
                Forms\Components\Select::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        'online' => 'Online',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('transaction_reference')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('payment_proof_path')
                    ->directory('contribution-proofs')
                    ->visibility('private')
                    ->preserveFilenames(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_anonymous')
                    ->required(),
                Forms\Components\Textarea::make('message')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
            ->columns([
                // Tables\Columns\TextColumn::make('parent.name')
                // ->visible(fn ($record) => !$record->is_anonymous),
            Tables\Columns\TextColumn::make('amount')
                ->money('NGN'),
            Tables\Columns\TextColumn::make('payment_method')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'cash' => 'gray',
                    'bank_transfer' => 'primary',
                    'online' => 'success',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'gray',
                }),
            Tables\Columns\IconColumn::make('is_anonymous')
                ->boolean(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($record) {
                        $record->update(['status' => 'approved']);
                        // Update the program's amount raised
                        $program = $record->fundraisingProgram;
                        $program->amount_raised += $record->amount;
                        $program->save();
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
