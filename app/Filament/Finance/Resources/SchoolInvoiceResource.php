<?php

namespace App\Filament\Finance\Resources;

use App\Filament\Finance\Resources\SchoolInvoiceResource\Pages;
use App\Filament\Finance\Resources\SchoolInvoiceResource\RelationManagers;
use App\Models\SchoolInvoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SchoolInvoiceResource extends Resource
{
    protected static ?string $model = SchoolInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Information')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->relationship('student', 'name')
                            ->required(),
                        Forms\Components\Select::make('academic_year_id')
                            ->relationship('academicYear', 'title')
                            ->required(),
                        Forms\Components\Select::make('term_id')
                            ->relationship('term', 'name')
                            ->required(),
                        Forms\Components\DatePicker::make('issue_date')
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')
                            ->required(),
                        Forms\Components\TextInput::make('total_amount')
                            ->required()
                            ->numeric()
                            ->prefix('₦'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Sent',
                                'paid' => 'Paid',
                                'partial' => 'Partial',
                                'overdue' => 'Overdue',
                            ])
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicYear.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('term.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->money('NGN'),
                Tables\Columns\TextColumn::make('balance')
                    ->money('NGN'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'paid' => 'success',
                        'partial' => 'warning',
                        'overdue' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->relationship('academicYear', 'title'),
                Tables\Filters\SelectFilter::make('term_id')
                    ->relationship('term', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'paid' => 'Paid',
                        'partial' => 'Partial',
                        'overdue' => 'Overdue',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('generate_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (SchoolInvoice $record) => route('invoices.pdf', $record))
                    ->openUrlInNewTab(),
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
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolInvoices::route('/'),
            'create' => Pages\CreateSchoolInvoice::route('/create'),
            'view' => Pages\ViewSchoolInvoice::route('/{record}'),
            'edit' => Pages\EditSchoolInvoice::route('/{record}/edit'),
        ];
    }
}
