<?php

namespace App\Filament\Library\Resources\LibraryBookLoanResource\Pages;

use App\Filament\Library\Resources\LibraryBookLoanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLibraryBookLoan extends ViewRecord
{
    protected static string $resource = LibraryBookLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
