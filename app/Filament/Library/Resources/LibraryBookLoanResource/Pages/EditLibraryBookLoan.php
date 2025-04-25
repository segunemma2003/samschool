<?php

namespace App\Filament\Library\Resources\LibraryBookLoanResource\Pages;

use App\Filament\Library\Resources\LibraryBookLoanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLibraryBookLoan extends EditRecord
{
    protected static string $resource = LibraryBookLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
