<?php

namespace App\Filament\Ourstudent\Resources\SchoolClassResource\Pages;

use App\Filament\Ourstudent\Resources\SchoolClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolClass extends EditRecord
{
    protected static string $resource = SchoolClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
