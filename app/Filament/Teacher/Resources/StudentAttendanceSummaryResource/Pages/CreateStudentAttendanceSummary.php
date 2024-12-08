<?php

namespace App\Filament\Teacher\Resources\StudentAttendanceSummaryResource\Pages;

use App\Filament\Teacher\Resources\StudentAttendanceSummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStudentAttendanceSummary extends CreateRecord
{
    protected static string $resource = StudentAttendanceSummaryResource::class;
}
