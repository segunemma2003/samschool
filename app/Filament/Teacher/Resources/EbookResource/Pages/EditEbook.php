<?php

namespace App\Filament\Teacher\Resources\EbookResource\Pages;

use App\Filament\Teacher\Resources\EbookResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEbook extends EditRecord
{
    protected static string $resource = EbookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
