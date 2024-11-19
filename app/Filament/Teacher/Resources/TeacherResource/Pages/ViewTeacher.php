<?php

namespace App\Filament\Teacher\Resources\TeacherResource\Pages;

use App\Filament\Teacher\Resources\TeacherResource;
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
