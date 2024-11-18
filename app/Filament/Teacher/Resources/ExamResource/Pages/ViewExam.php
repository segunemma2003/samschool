<?php

namespace App\Filament\Teacher\Resources\ExamResource\Pages;

use App\Filament\Teacher\Resources\ExamResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExam extends ViewRecord
{
    protected static string $resource = ExamResource::class;

    protected static string $view = "filament.teacher.resources.pages.exam";

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
