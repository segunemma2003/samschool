<?php

namespace App\Filament\Teacher\Resources\CourseFormResource\Pages;

use App\Filament\Teacher\Resources\CourseFormResource;
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
