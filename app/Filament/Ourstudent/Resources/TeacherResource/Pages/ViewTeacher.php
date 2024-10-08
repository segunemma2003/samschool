<?php

namespace App\Filament\Ourstudent\Resources\TeacherResource\Pages;

use App\Filament\Ourstudent\Resources\TeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTeacher extends ViewRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
