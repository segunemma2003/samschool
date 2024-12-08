<?php

namespace App\Filament\Teacher\Resources\PsychomotorStudentResource\Pages;

use App\Filament\Teacher\Resources\PsychomotorStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPsychomotorStudents extends ListRecords
{
    protected static string $resource = PsychomotorStudentResource::class;

    protected static string $view ="filament.teacher.resources.pyschomotor-resource.list-item";

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
