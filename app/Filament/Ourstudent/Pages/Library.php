<?php

namespace App\Filament\Ourstudent\Pages;

use App\Models\LibraryBook;
use App\Models\LibraryBookCategories;
use App\Models\LibraryBookLoan;
use App\Models\LibraryBookRequest;
use App\Models\LibraryLocation;
use App\Models\Student;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;


class Library extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup   = 'Library';

    protected static string $view = 'filament.ourstudent.pages.library';


    #[Url]
    public string $activeTab = 'books';

    public $selectedLoan;

    public $showReturnModal = false;
    public $bookConditionGood = true;
    public $returnNotes = '';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function table(Table $table): Table
    {
        return $table
        ->query(LibraryBook::query()->with(['category', 'shelf.location']))
            ->columns([
                ImageColumn::make('cover_image')
                    ->disk('s3')
                    ->label('Cover')
                    ->width(50)
                    ->height(70),
                    TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('author')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Category'),
                TextColumn::make('full_location')
                    ->label('Location')
                    ->sortable(query: function (Builder $query, string $direction) {
                        $query->orderBy(
                            LibraryLocation::select('name')
                                ->whereColumn('library_locations.id', 'library_shelves.location_id')
                                ->join('library_shelves', 'library_shelves.id', '=', 'library_books.shelf_id'),
                            $direction
                        );
                    }),
                TextColumn::make('available_copies')
                    ->label('Available'),
            ])
            ->actions([
                Action::make('borrow')
                    ->form([
                        DatePicker::make('desired_loan_date')
                            ->required()
                            ->minDate(now()),
                        Textarea::make('reason')
                            ->required(),
                    ])
                    ->action(function (LibraryBook $book, array $data): void {
                        LibraryBookRequest::create([
                            'library_book_id' => $book->id,
                            'requester_type' => Student::class,
                            'requester_id' => Auth::id(),
                            'status' => 'pending',
                            'reason' => $data['reason'],
                        ]);

                        $this->notify('success', 'Book request submitted successfully');
                    })
                    ->visible(fn (LibraryBook $book) => $book->available_copies > 0),
                Action::make('request')
                    ->form([
                        DatePicker::make('desired_loan_date')
                            ->required(),
                        Textarea::make('reason')
                            ->required(),
                    ])
                    ->action(function (LibraryBook $book, array $data): void {
                        $user = User::whereId(Auth::id())->first();
                        $student = Student::whereEmail($user->email)->first();
                        LibraryBookRequest::create([
                            'library_book_id' => $book->id,
                            'requester_type' => Student::class,
                            'requester_id' => $student->id,
                            'status' => 'pending',
                            'reason' => $data['reason'],
                        ]);
                    })
                    ->visible(fn (LibraryBook $book) => $book->quantity > 0),
            ]);
    }


    public function getCurrentLoansProperty()
    {
        return LibraryBookLoan::where('borrower_type', Student::class)
            ->where('borrower_id', Auth::id())
            ->where('status', '!=', 'returned')
            ->with(['book.category', 'book.shelf.location'])
            ->get();
    }

    public function getBookRequestsProperty()
    {
        return LibraryBookRequest::where('requester_type', Student::class)
            ->where('requester_id', Auth::id())
            ->with(['book.category', 'book.shelf.location'])
            ->latest()
            ->get();
    }

    public function getCategoriesProperty()
    {
        return LibraryBookCategories::all();
    }

    public function getLocationsProperty()
    {
        return LibraryLocation::with('shelves')->get();
    }

    public function showReturnModal($loanId): void
    {
        $this->selectedLoan = LibraryBookLoan::find($loanId);
        $this->showReturnModal = true;
    }

    public function confirmReturn(): void
    {
        $this->validate([
            'bookConditionGood' => ['required', 'boolean'],
            'returnNotes' => ['nullable', 'string'],
        ]);

        $this->selectedLoan->update([
            'return_date' => now(),
            'status' => 'returned',
            'notes' => $this->returnNotes,
        ]);

        $this->selectedLoan->book->increment('quantity');

        $this->showReturnModal = false;
        $this->notify('success', 'Book returned successfully');
    }

    public function requestExtension($loanId): void
    {
        $loan = LibraryBookLoan::find($loanId);
        $loan->update(['due_date' => $loan->due_date->addWeek()]);

        $this->notify('success', 'Loan extended by 1 week');
    }

    public function cancelRequest($requestId): void
    {
        LibraryBookRequest::find($requestId)->delete();
        $this->notify('success', 'Request cancelled');
    }

    public function getBorrowedBooksTable(): Table
    {
        return Table::
            query(LibraryBookLoan::where('borrower_type', Student::class)
                ->where('borrower_id', Auth::id())
                ->where('status', '!=', 'returned'))
            ->columns([
                TextColumn::make('book.title'),
                TextColumn::make('loan_date')
                    ->date(),
                TextColumn::make('due_date')
                    ->date()
                    ->color(fn (LibraryBookLoan $record) => $record->status === 'overdue' ? 'danger' : null),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'borrowed' => 'info',
                        'overdue' => 'danger',
                        'lost' => 'warning',
                    }),
            ])
            ->actions([
                Action::make('return')
                    ->form([
                        DatePicker::make('return_date')
                            ->default(now())
                            ->required(),
                        Textarea::make('notes'),
                    ])
                    ->action(function (LibraryBookLoan $record, array $data): void {
                        $record->update([
                            'return_date' => $data['return_date'],
                            'status' => 'returned',
                            'notes' => $data['notes'],
                        ]);

                        $record->book()->increment('quantity');
                    })
                    ->visible(fn (LibraryBookLoan $record) => $record->status !== 'returned'),
            ]);
    }
}
