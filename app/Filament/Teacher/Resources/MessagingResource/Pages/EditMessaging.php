<?php

namespace App\Filament\Teacher\Resources\MessagingResource\Pages;

use App\Filament\Teacher\Resources\MessagingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMessaging extends EditRecord
{
    protected static string $resource = MessagingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
