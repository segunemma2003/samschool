<?php

namespace App\Filament\Ourstudent\Resources\CourseFormResource\Pages;

use App\Filament\Ourstudent\Resources\CourseFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseForm extends EditRecord
{
    protected static string $resource = CourseFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
