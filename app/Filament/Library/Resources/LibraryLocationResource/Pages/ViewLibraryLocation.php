<?php

namespace App\Filament\Library\Resources\LibraryLocationResource\Pages;

use App\Filament\Library\Resources\LibraryLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLibraryLocation extends ViewRecord
{
    protected static string $resource = LibraryLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
