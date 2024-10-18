<?php

namespace App\Filament\Teacher\Resources\MessagingResource\Pages;

use App\Filament\Teacher\Resources\MessagingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMessagings extends ListRecords
{
    protected static string $resource = MessagingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
