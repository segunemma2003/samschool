<?php

namespace App\Filament\App\Resources\StudentResource\Pages;

use App\Filament\App\Resources\StudentResource;
use App\Filament\Imports\StudentImporter;
use App\Models\Student;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {

        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make()
                ->importer(StudentImporter::class),
        ];
    }
}
