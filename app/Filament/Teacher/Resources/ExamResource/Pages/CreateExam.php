<?php

namespace App\Filament\Teacher\Resources\ExamResource\Pages;

use App\Filament\Teacher\Resources\ExamResource;
use App\Models\AcademicYear;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExam extends CreateRecord
{
    protected static string $resource = ExamResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $academy = AcademicYear::whereStatus('true')->first();
        $data['academic_year_id'] = $academy->id ?? 1;


        return $data;
    }
}
