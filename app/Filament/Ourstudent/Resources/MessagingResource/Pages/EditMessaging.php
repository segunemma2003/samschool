<?php

namespace App\Filament\Ourstudent\Resources\MessagingResource\Pages;

use App\Filament\Ourstudent\Resources\MessagingResource;
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
