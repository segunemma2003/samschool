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
        $user = \App\Models\User::whereId(\Illuminate\Support\Facades\Auth::id())->first();
        $cacheKey = "library_overview_stats";
        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($user) {
            return [
                'total_books' => \App\Models\LibraryBookLoan::count(),
                'borrowed' => \App\Models\LibraryBookLoan::where('borrower_type', $user->getMorphClass())
                    ->where('status', 'borrowed')
                    ->count(),
                'overdue' => \App\Models\LibraryBookLoan::where('borrower_type', $user->getMorphClass())
                    ->where('status', 'overdue')
                    ->count(),
                'returned' => \App\Models\LibraryBookLoan::where('borrower_type', $user->getMorphClass())
                    ->where('status', 'returned')
                    ->count(),
                'total_locations' => \App\Models\LibraryLocation::count(),
                'total_shelves' => \App\Models\LibraryShelf::count(),
                'avg_books_per_shelf' => number_format(\App\Models\LibraryShelf::withCount('books')->get()->avg('books_count'), 1),
                'books_without_location' => \App\Models\LibraryBook::whereNull('shelf_id')->count(),
            ];
        });
        return [
            Stat::make(' Total Books', $stats['total_books']),
            Stat::make('Books Borrowed', $stats['borrowed']),
            Stat::make('Overdue Books', $stats['overdue']),
            Stat::make('Books Returned', $stats['returned']),
            Stat::make('Total Locations', $stats['total_locations']),
            Stat::make('Total Shelves', $stats['total_shelves']),
            Stat::make('Average Books per Shelf', $stats['avg_books_per_shelf']),
            Stat::make('Books Without Location', $stats['books_without_location']),
        ];
    }
}
