<?php

namespace App\Filament\Library\Resources;

use App\Filament\Library\Resources\LibraryBookResource\Pages;
use App\Filament\Library\Resources\LibraryBookResource\RelationManagers;
use App\Models\LibraryBook;
use App\Models\LibraryShelf;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class LibraryBookResource extends Resource
{
    protected static ?string $model = LibraryBook::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('author')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('isbn')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('shelf_id')
                    ->relationship('shelf', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $set('row_number', null);
                        $set('position_number', null);
                    }),

                    Forms\Components\Select::make('row_number')
                    ->options(function (Forms\Get $get) {
                        $shelf = LibraryShelf::find($get('shelf_id'));
                        if (!$shelf) return [];

                        return array_combine(
                            range(1, $shelf->row_count),
                            array_map(fn($n) => "Row $n", range(1, $shelf->row_count))
                        );
                    })
                    ->requiredWith('shelf_id'),
                Forms\Components\Select::make('position_number')
                    ->options(function (Forms\Get $get) {
                        $shelf = LibraryShelf::find($get('shelf_id'));
                        if (!$shelf) return [];

                        return array_combine(
                            range(1, $shelf->position_count),
                            array_map(fn($n) => "Position $n", range(1, $shelf->position_count))
                        );
                    })
                    ->requiredWith('shelf_id'),
                Forms\Components\Select::make('library_category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535),
                    ]),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535),
                Forms\Components\FileUpload::make('cover_image')
                    ->image()
                    ->disk('s3')
                    ->directory('book-covers'),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        $user = User::whereId(Auth::id())->first();
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                ->label('Cover'),
            Tables\Columns\TextColumn::make('title')
                ->searchable(),
            Tables\Columns\TextColumn::make('author')
                ->searchable(),
            Tables\Columns\TextColumn::make('category.name'),
            Tables\Columns\TextColumn::make('quantity'),
            Tables\Columns\TextColumn::make('full_location')
            ->label('Location')
            ->sortable(query: function (Builder $query, string $direction) {
                $query->orderBy(
                    LibraryShelf::select('code')
                        ->whereColumn('library_shelves.id', 'library_books.shelf_id'),
                    $direction
                );
            }),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
            ])
            ->actions([
                Tables\Actions\Action::make('assign_location')
                    ->form([
                        Forms\Components\Select::make('shelf_id')
                            ->relationship('shelf', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('row_number')
                            ->options(function (Forms\Get $get) {
                                $shelf = LibraryShelf::find($get('shelf_id'));
                                return $shelf
                                    ? array_combine(
                                        range(1, $shelf->row_count),
                                        array_map(fn($n) => "Row $n", range(1, $shelf->row_count))
                                    )
                                    : [];
                            })
                            ->required(),
                        Forms\Components\Select::make('position_number')
                            ->options(function (Forms\Get $get) {
                                $shelf = LibraryShelf::find($get('shelf_id'));
                                return $shelf
                                    ? array_combine(
                                        range(1, $shelf->position_count),
                                        array_map(fn($n) => "Position $n", range(1, $shelf->position_count))
                                    )
                                    : [];
                            })
                            ->required(),
                    ])
                    ->action(function (array $data, $records): void {
                        foreach ($records as $record) {
                            $record->update([
                                'shelf_id' => $data['shelf_id'],
                                'row_number' => $data['row_number'],
                                'position_number' => $data['position_number'],
                            ]);
                        }
                    }),
                Tables\Actions\Action::make('borrow')
                    ->form([
                        Forms\Components\Select::make('borrower_type')
                            ->options([
                                'student' => 'Student',
                                'teacher' => 'Teacher',
                            ])
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
                        Forms\Components\DatePicker::make('due_date')
                            ->required()
                            ->minDate(now()),
                    ])
                    ->action(function (LibraryBook $book, array $data): void {
                        $book->loans()->create([
                            'borrower_type' => $data['borrower_type'],
                            'borrower_id' => $data['borrower_id'],
                            'loan_date' => now(),
                            'due_date' => $data['due_date'],
                        ]);

                        $book->decrement('quantity');
                    })
                    ->visible(fn () => $user->user_type == 'admin'),
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
            RelationManagers\LoansRelationManager::class,
            RelationManagers\RequestsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLibraryBooks::route('/'),
            'create' => Pages\CreateLibraryBook::route('/create'),
            'view' => Pages\ViewLibraryBook::route('/{record}'),
            'edit' => Pages\EditLibraryBook::route('/{record}/edit'),
        ];
    }
}
