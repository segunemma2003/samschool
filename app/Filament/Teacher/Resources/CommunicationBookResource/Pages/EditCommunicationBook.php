<?php

namespace App\Filament\Teacher\Resources\CommunicationBookResource\Pages;

use App\Filament\Teacher\Resources\CommunicationBookResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCommunicationBook extends EditRecord
{
    protected static string $resource = CommunicationBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
