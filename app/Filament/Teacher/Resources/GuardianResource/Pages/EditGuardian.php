<?php

namespace App\Filament\Teacher\Resources\GuardianResource\Pages;

use App\Filament\Teacher\Resources\GuardianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuardian extends EditRecord
{
    protected static string $resource = GuardianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
