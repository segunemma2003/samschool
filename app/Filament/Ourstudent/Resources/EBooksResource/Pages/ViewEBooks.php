<?php

namespace App\Filament\Ourstudent\Resources\EbooksResource\Pages;

use App\Filament\Ourstudent\Resources\EbooksResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEbooks extends ViewRecord
{
    protected static string $resource = EbooksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }
}
