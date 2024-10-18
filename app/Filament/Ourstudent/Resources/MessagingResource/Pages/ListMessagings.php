<?php

namespace App\Filament\Ourstudent\Resources\MessagingResource\Pages;

use App\Filament\Ourstudent\Resources\MessagingResource;
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
