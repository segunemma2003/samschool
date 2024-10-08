<?php

namespace App\Filament\App\Resources\SchoolClassResource\Pages;

use App\Filament\App\Resources\SchoolClassResource;
use App\Filament\Imports\SchoolClassImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolClasses extends ListRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make()
            ->importer(SchoolClassImporter::class),
        ];
    }
}
