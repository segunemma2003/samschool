<?php

namespace App\Filament\App\Resources\ResultSectionTypeResource\Pages;

use App\Filament\App\Resources\ResultSectionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResultSectionType extends EditRecord
{
    protected static string $resource = ResultSectionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
