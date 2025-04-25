<?php

namespace App\Filament\Library\Widgets;

use App\Models\Library;
use App\Models\LibraryBookLoan;
use App\Models\LibraryLocation;
use App\Models\LibraryShelf;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class LibraryOverview extends BaseWidget
{
    protected ?string $heading = 'Library Analytics';

    protected ?string $description = 'An overview of the Library';

    protected function getStats(): array
    {
        $user = User::whereId(Auth::id())->first();


        return [
            Stat::make(' Total Books', LibraryBookLoan::count())
            ,
            Stat::make('Books Borrowed', LibraryBookLoan::where('borrower_type', $user->getMorphClass())
                // ->where('borrower_id', $user->id)
                ->where('status', 'borrowed')
                ->count()),
            Stat::make('Overdue Books', LibraryBookLoan::where('borrower_type', $user->getMorphClass())
                // ->where('borrower_id', $user->id)
                ->where('status', 'overdue')
                ->count()),
            Stat::make('Books Returned', LibraryBookLoan::where('borrower_type', $user->getMorphClass())
                // ->where('borrower_id', $user->id)
                ->where('status', 'returned')
                ->count()),
                Stat::make('Total Locations', LibraryLocation::count()),
                Stat::make('Total Shelves', LibraryShelf::count()),
                Stat::make('Average Books per Shelf',
                    number_format(LibraryShelf::withCount('books')->get()->avg('books_count'), 1)),
                Stat::make('Books Without Location',
                    \App\Models\LibraryBook::whereNull('shelf_id')->count()),
        ];
    }
}
