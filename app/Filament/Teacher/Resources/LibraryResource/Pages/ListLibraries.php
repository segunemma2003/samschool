<?php

namespace App\Filament\Teacher\Resources\LibraryResource\Pages;

use App\Filament\Teacher\Resources\LibraryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLibraries extends ListRecords
{
    protected static string $resource = LibraryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
