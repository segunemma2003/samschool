<?php

namespace App\Filament\Library\Resources\LibraryLocationResource\Pages;

use App\Filament\Library\Resources\LibraryLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLibraryLocations extends ListRecords
{
    protected static string $resource = LibraryLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
