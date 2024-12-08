<?php

namespace App\Filament\Teacher\Resources\StudentAttendanceSummaryResource\Pages;

use App\Filament\Teacher\Resources\StudentAttendanceSummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentAttendanceSummary extends ViewRecord
{
    protected static string $resource = StudentAttendanceSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
