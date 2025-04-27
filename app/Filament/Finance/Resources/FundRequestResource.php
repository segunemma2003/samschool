<?php

namespace App\Filament\Finance\Resources;

use App\Filament\Finance\Resources\FundRequestResource\Pages;
use App\Filament\Finance\Resources\FundRequestResource\RelationManagers;
use App\Models\FundRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class FundRequestResource extends Resource
{
    protected static ?string $model = FundRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Fund Request Information')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('₦'),
                        Forms\Components\Textarea::make('purpose')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->visible(fn (Forms\Get $get) => $get('status') === 'rejected')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('payment_proof_path')
                            ->directory('fund-request-proofs')
                            ->visibility('private')
                            ->disk('s3')
                            ->preserveFilenames()
                            ->visible(fn (Forms\Get $get) => $get('status') === 'approved'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (FundRequest $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);
                    })
                    ->visible(fn (FundRequest $record) => $record->status === 'pending'),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (FundRequest $record) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);
                    })
                    ->visible(fn (FundRequest $record) => $record->status === 'pending' ),
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
            'index' => Pages\ListFundRequests::route('/'),
            'create' => Pages\CreateFundRequest::route('/create'),
            'view' => Pages\ViewFundRequest::route('/{record}'),
            'edit' => Pages\EditFundRequest::route('/{record}/edit'),
        ];
    }
}
