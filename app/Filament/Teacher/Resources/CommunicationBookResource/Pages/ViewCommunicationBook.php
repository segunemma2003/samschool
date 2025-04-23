<?php

namespace App\Filament\Teacher\Resources\CommunicationBookResource\Pages;

use App\Filament\Teacher\Resources\CommunicationBookResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCommunicationBook extends ViewRecord
{
    protected static string $resource = CommunicationBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
