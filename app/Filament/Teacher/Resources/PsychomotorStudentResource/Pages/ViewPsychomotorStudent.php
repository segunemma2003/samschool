<?php

namespace App\Filament\Teacher\Resources\PsychomotorStudentResource\Pages;

use App\Filament\Teacher\Resources\PsychomotorStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPsychomotorStudent extends ViewRecord
{
    protected static string $resource = PsychomotorStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
