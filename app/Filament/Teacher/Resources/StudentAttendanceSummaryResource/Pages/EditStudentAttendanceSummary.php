<?php

namespace App\Filament\Teacher\Resources\StudentAttendanceSummaryResource\Pages;

use App\Filament\Teacher\Resources\StudentAttendanceSummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentAttendanceSummary extends EditRecord
{
    protected static string $resource = StudentAttendanceSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
