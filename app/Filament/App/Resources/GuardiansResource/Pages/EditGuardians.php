<?php

namespace App\Filament\App\Resources\GuardiansResource\Pages;

use App\Filament\App\Resources\GuardiansResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuardians extends EditRecord
{
    protected static string $resource = GuardiansResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
