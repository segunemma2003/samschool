<?php

namespace App\Filament\Ourstudent\Resources\CourseFormResource\Pages;

use App\Filament\Ourstudent\Resources\CourseFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCourseForm extends ViewRecord
{
    protected static string $resource = CourseFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
