<?php

namespace App\Filament\App\Resources\SchoolSectionResource\Pages;

use App\Filament\App\Resources\SchoolSectionResource;
use App\Filament\Imports\SchoolSectionImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolSections extends ListRecords
{
    protected static string $resource = SchoolSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make()
            ->importer(SchoolSectionImporter::class),
        ];
    }
}
