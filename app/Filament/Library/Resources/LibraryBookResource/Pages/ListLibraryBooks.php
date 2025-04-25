<?php

namespace App\Filament\Library\Resources\LibraryBookResource\Pages;

use App\Filament\Library\Resources\LibraryBookResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLibraryBooks extends ListRecords
{
    protected static string $resource = LibraryBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
