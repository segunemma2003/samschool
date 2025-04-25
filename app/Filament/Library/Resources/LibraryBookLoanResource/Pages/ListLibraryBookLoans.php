<?php

namespace App\Filament\Library\Resources\LibraryBookLoanResource\Pages;

use App\Filament\Library\Resources\LibraryBookLoanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLibraryBookLoans extends ListRecords
{
    protected static string $resource = LibraryBookLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
