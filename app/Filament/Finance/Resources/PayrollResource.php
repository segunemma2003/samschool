<?php

namespace App\Filament\Finance\Resources;

use App\Filament\Finance\Resources\PayrollResource\Pages;
use App\Filament\Finance\Resources\PayrollResource\RelationManagers;
use App\Models\Payroll;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payroll Information')
                ->schema([
                    Forms\Components\Select::make('staff_salary_id')
                        ->relationship('staffSalary', 'id')
                        ->required(),
                    Forms\Components\Select::make('month')
                        ->options([
                            'January' => 'January',
                            'February' => 'February',
                            'March' => 'March',
                            'April' => 'April',
                            'May' => 'May',
                            'June' => 'June',
                            'July' => 'July',
                            'August' => 'August',
                            'September' => 'September',
                            'October' => 'October',
                            'November' => 'November',
                            'December' => 'December',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('year')
                        ->required()
                        ->numeric()
                        ->length(4),
                    Forms\Components\TextInput::make('basic_salary')
                        ->required()
                        ->numeric()
                        ->prefix('₦'),
                    Forms\Components\TextInput::make('total_allowances')
                        ->required()
                        ->numeric()
                        ->prefix('₦'),
                    Forms\Components\TextInput::make('total_deductions')
                        ->required()
                        ->numeric()
                        ->prefix('₦'),
                    Forms\Components\TextInput::make('net_salary')
                        ->required()
                        ->numeric()
                        ->prefix('₦'),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required(),
                    Forms\Components\DateTimePicker::make('paid_at')
                        ->visible(fn (Forms\Get $get) => $get('status') === 'paid'),
                    Forms\Components\Select::make('payment_method')
                        ->options([
                            'cash' => 'Cash',
                            'bank_transfer' => 'Bank Transfer',
                            'online' => 'Online',
                        ])
                        ->visible(fn (Forms\Get $get) => $get('status') === 'paid'),
                    Forms\Components\TextInput::make('transaction_reference')
                        ->maxLength(255)
                        ->visible(fn (Forms\Get $get) => $get('status') === 'paid'),
                    Forms\Components\Textarea::make('notes')
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staffSalary.staff.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('month'),
                Tables\Columns\TextColumn::make('year'),
                Tables\Columns\TextColumn::make('basic_salary')
                    ->money('NGN'),
                Tables\Columns\TextColumn::make('total_allowances')
                    ->money('NGN'),
                Tables\Columns\TextColumn::make('total_deductions')
                    ->money('NGN'),
                Tables\Columns\TextColumn::make('net_salary')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('paid_at')
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
                Tables\Filters\SelectFilter::make('month')
                    ->options([
                        'January' => 'January',
                        'February' => 'February',
                        'March' => 'March',
                        'April' => 'April',
                        'May' => 'May',
                        'June' => 'June',
                        'July' => 'July',
                        'August' => 'August',
                        'September' => 'September',
                        'October' => 'October',
                        'November' => 'November',
                        'December' => 'December',
                    ]),
                Tables\Filters\SelectFilter::make('year')
                    ->options(function () {
                        $years = range(date('Y') - 5, date('Y') + 5);
                        return array_combine($years, $years);
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('generate_payslip')
                ->label('Payslip')
                ->icon('heroicon-o-document-text')
                ->url(fn (Payroll $record) => route('payslips.pdf', $record))
                ->openUrlInNewTab()
                ->visible(fn (Payroll $record) => $record->payslip !== null),
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
            RelationManagers\PayslipRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'view' => Pages\ViewPayroll::route('/{record}'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
