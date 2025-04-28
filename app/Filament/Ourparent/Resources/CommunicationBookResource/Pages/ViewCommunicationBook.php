<?php

namespace App\Filament\Ourparent\Resources\CommunicationBookResource\Pages;

use App\Filament\Ourparent\Resources\CommunicationBookResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCommunicationBook extends ViewRecord
{
    protected static string $resource = CommunicationBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }
}
