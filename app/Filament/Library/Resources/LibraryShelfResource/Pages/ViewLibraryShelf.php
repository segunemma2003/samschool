<?php

namespace App\Filament\Library\Resources\LibraryShelfResource\Pages;

use App\Filament\Library\Resources\LibraryShelfResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLibraryShelf extends ViewRecord
{
    protected static string $resource = LibraryShelfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
