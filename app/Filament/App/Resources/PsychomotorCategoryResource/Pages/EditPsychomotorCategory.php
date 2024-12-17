<?php

namespace App\Filament\App\Resources\PsychomotorCategoryResource\Pages;

use App\Filament\App\Resources\PsychomotorCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPsychomotorCategory extends EditRecord
{
    protected static string $resource = PsychomotorCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
