<?php

namespace App\Filament\Ourstudent\Resources\SchoolSectionResource\Pages;

use App\Filament\Ourstudent\Resources\SchoolSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolSections extends ListRecords
{
    protected static string $resource = SchoolSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
