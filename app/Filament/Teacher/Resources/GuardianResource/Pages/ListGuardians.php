<?php

namespace App\Filament\Teacher\Resources\GuardianResource\Pages;

use App\Filament\Teacher\Resources\GuardianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuardians extends ListRecords
{
    protected static string $resource = GuardianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
