<?php

namespace App\Filament\Ourparent\Resources\MessagingResource\Pages;

use App\Filament\Ourparent\Resources\MessagingResource;
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
