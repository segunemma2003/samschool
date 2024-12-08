<?php

namespace App\Filament\Teacher\Resources\StudentAttendanceSummaryResource\Pages;

use App\Filament\Teacher\Resources\StudentAttendanceSummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentAttendanceSummaries extends ListRecords
{
    protected static string $resource = StudentAttendanceSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
