<?php

namespace App\Filament\App\Resources\SchoolInformationResource\Pages;

use App\Filament\App\Resources\SchoolInformationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolInformation extends EditRecord
{
    protected static string $resource = SchoolInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
