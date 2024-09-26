<?php

namespace App\Filament\App\Resources\StudentGroupResource\Pages;

use App\Filament\App\Resources\StudentGroupResource;
use App\Filament\Imports\StudentGroupImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentGroups extends ListRecords
{
    protected static string $resource = StudentGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make()
            ->importer(StudentGroupImporter::class),
        ];
    }
}
