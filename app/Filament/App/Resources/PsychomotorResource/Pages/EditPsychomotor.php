<?php

namespace App\Filament\App\Resources\PsychomotorResource\Pages;

use App\Filament\App\Resources\PsychomotorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPsychomotor extends EditRecord
{
    protected static string $resource = PsychomotorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
