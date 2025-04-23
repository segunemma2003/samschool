<?php

namespace App\Filament\Ourparent\Resources\CommunicationBookResource\Pages;

use App\Filament\Ourparent\Resources\CommunicationBookResource;
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
}
