<?php

namespace App\Filament\Ourstudent\Resources\SchoolClassResource\Pages;

use App\Filament\Ourstudent\Resources\SchoolClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSchoolClass extends ViewRecord
{
    protected static string $resource = SchoolClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
