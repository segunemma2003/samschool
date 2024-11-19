<?php

namespace App\Filament\Teacher\Resources\GuardianResource\Pages;

use App\Filament\Teacher\Resources\GuardianResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGuardian extends ViewRecord
{
    protected static string $resource = GuardianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
