<?php

namespace App\Filament\Ourstudent\Resources\SchoolSectionResource\Pages;

use App\Filament\Ourstudent\Resources\SchoolSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolSection extends EditRecord
{
    protected static string $resource = SchoolSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
