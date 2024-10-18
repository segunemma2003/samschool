<?php

namespace App\Filament\Ourparent\Resources\StudentAttendanceResource\Pages;

use App\Filament\Ourparent\Resources\StudentAttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentAttendances extends ListRecords
{
    protected static string $resource = StudentAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
