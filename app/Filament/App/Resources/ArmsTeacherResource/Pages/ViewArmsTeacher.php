<?php

namespace App\Filament\App\Resources\ArmsTeacherResource\Pages;

use App\Filament\App\Resources\ArmsTeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewArmsTeacher extends ViewRecord
{
    protected static string $resource = ArmsTeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
