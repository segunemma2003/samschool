<?php

namespace App\Filament\Ourstudent\Resources\SchoolSectionResource\Pages;

use App\Filament\Ourstudent\Resources\SchoolSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSchoolSection extends ViewRecord
{
    protected static string $resource = SchoolSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}