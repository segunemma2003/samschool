<?php

namespace App\Filament\Teacher\Resources\EbookResource\Pages;

use App\Filament\Teacher\Resources\EbookResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEbook extends ViewRecord
{
    protected static string $resource = EbookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
