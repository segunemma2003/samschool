<?php

namespace App\Filament\Ourstudent\Resources\EbooksResource\Pages;

use App\Filament\Ourstudent\Resources\EbooksResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEbooks extends EditRecord
{
    protected static string $resource = EbooksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\ViewAction::make(),
            // Actions\DeleteAction::make(),
        ];
    }
}
