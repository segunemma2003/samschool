<?php

namespace App\Filament\Ourstudent\Resources\EBooksResource\Pages;

use App\Filament\Ourstudent\Resources\EBooksResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEBooks extends ListRecords
{
    protected static string $resource = EBooksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
