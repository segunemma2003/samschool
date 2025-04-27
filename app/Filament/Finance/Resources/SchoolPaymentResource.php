<?php

namespace App\Filament\Finance\Resources;

use App\Filament\Finance\Resources\SchoolPaymentResource\Pages;
use App\Filament\Finance\Resources\SchoolPaymentResource\RelationManagers;
use App\Models\SchoolPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SchoolPaymentResource extends Resource
{
    protected static ?string $model = SchoolPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('payable_type')
                            ->options([
                                'student_fee' => 'Student Fee',
                                'program_contribution' => 'Program Contribution',
                            ])
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('payable_id')
                            ->label('Payable')
                            ->options(function (Forms\Get $get) {
                                if ($get('payable_type') === 'student_fee') {
                                    return \App\Models\StudentFee::all()->pluck('id', 'id');
                                } elseif ($get('payable_type') === 'program_contribution') {
                                    return \App\Models\ProgramContribution::all()->pluck('id', 'id');
                                }
                                return [];
                            })
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
                            ->directory('payment-proofs')
                            ->visibility('private')
                            ->preserveFilenames(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                        Forms\Components\Select::make('approved_by')
                            ->relationship('approvedBy', 'name')
                            ->visible(fn (Forms\Get $get) => $get('status') === 'approved'),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->visible(fn (Forms\Get $get) => $get('status') === 'approved'),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payable_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'student_fee' => 'info',
                        'program_contribution' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->money('NGN')
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('payable_type')
                    ->options([
                        'student_fee' => 'Student Fee',
                        'program_contribution' => 'Program Contribution',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        'online' => 'Online',
                    ]),
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
                    ->action(function (SchoolPayment $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);
                    })
                    ->visible(fn (SchoolPayment $record) => $record->status === 'pending'),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (SchoolPayment $record) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);
                    })
                    ->visible(fn (SchoolPayment $record) => $record->status === 'pending'),
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
            'index' => Pages\ListSchoolPayments::route('/'),
            'create' => Pages\CreateSchoolPayment::route('/create'),
            'view' => Pages\ViewSchoolPayment::route('/{record}'),
            'edit' => Pages\EditSchoolPayment::route('/{record}/edit'),
        ];
    }
}
