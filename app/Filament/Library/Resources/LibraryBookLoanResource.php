<?php

namespace App\Filament\Library\Resources;

use App\Filament\Library\Resources\LibraryBookLoanResource\Pages;
use App\Filament\Library\Resources\LibraryBookLoanResource\RelationManagers;
use App\Models\LibraryBookLoan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LibraryBookLoanResource extends Resource
{
    protected static ?string $model = LibraryBookLoan::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Library';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('library_book_id')
                    ->relationship('book', 'title')
                    ->required(),
                Forms\Components\Select::make('borrower_type')
                    ->options([
                        'student' => 'Student',
                        'teacher' => 'Teacher',
                    ])
                    ->live()
                    ->required(),
                Forms\Components\Select::make('borrower_id')
                    ->label('Borrower')
                    ->options(function (callable $get) {
                        $type = $get('borrower_type');
                        if ($type === 'student') {
                            return \App\Models\Student::all()->pluck('name', 'id');
                        } elseif ($type === 'teacher') {
                            return \App\Models\Teacher::all()->pluck('name', 'id');
                        }
                        return [];
                    })
                    ->required(),
                Forms\Components\DatePicker::make('loan_date')
                    ->required(),
                Forms\Components\DatePicker::make('due_date')
                    ->required(),
                Forms\Components\DatePicker::make('return_date'),
                Forms\Components\Select::make('status')
                    ->options([
                        'borrowed' => 'Borrowed',
                        'returned' => 'Returned',
                        'overdue' => 'Overdue',
                        'lost' => 'Lost',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('notes'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                ->searchable(),
            Tables\Columns\TextColumn::make('borrower.name')
                ->label('Borrower'),
            Tables\Columns\TextColumn::make('loan_date')
                ->date(),
            Tables\Columns\TextColumn::make('due_date')
                ->date()
                ->color(fn (LibraryBookLoan $record) => $record->status === 'overdue' ? 'danger' : null),
            Tables\Columns\TextColumn::make('return_date')
                ->date(),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'borrowed' => 'info',
                    'returned' => 'success',
                    'overdue' => 'danger',
                    'lost' => 'warning',
                }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                ->options([
                    'borrowed' => 'Borrowed',
                    'returned' => 'Returned',
                    'overdue' => 'Overdue',
                    'lost' => 'Lost',
                ]),
            Tables\Filters\Filter::make('overdue')
                ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now())->where('status', '!=', 'returned')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('return')
                ->form([
                    Forms\Components\DatePicker::make('return_date')
                        ->default(now())
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'returned' => 'Returned',
                            'lost' => 'Lost',
                        ])
                        ->required(),
                    Forms\Components\Textarea::make('notes'),
                ])
                ->action(function (LibraryBookLoan $record, array $data): void {
                    $record->update([
                        'return_date' => $data['return_date'],
                        'status' => $data['status'],
                        'notes' => $data['notes'],
                    ]);

                    $record->book()->increment('quantity');
                })
                ->visible(fn (LibraryBookLoan $record) => $record->status !== 'returned'),
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
            'index' => Pages\ListLibraryBookLoans::route('/'),
            'create' => Pages\CreateLibraryBookLoan::route('/create'),
            'view' => Pages\ViewLibraryBookLoan::route('/{record}'),
            'edit' => Pages\EditLibraryBookLoan::route('/{record}/edit'),
        ];
    }
}
