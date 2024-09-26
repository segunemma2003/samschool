<?php

namespace App\Filament\App\Resources\TeacherResource\Pages;

use App\Filament\App\Resources\TeacherResource;
use App\Filament\Imports\TeacherImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeachers extends ListRecords
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make()
                ->importer(TeacherImporter::class),
        ];
    }
}
