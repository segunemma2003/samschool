<?php

namespace App\Filament\Ourstudent\Resources\EBooksResource\Pages;

use App\Filament\Ourstudent\Resources\EBooksResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEBooks extends ViewRecord
{
    protected static string $resource = EBooksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
