<?php

namespace App\Filament\App\Resources\GuardiansResource\Pages;

use App\Filament\App\Resources\GuardiansResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGuardians extends ViewRecord
{
    protected static string $resource = GuardiansResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
