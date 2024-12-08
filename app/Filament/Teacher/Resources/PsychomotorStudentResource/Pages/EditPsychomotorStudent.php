<?php

namespace App\Filament\Teacher\Resources\PsychomotorStudentResource\Pages;

use App\Filament\Teacher\Resources\PsychomotorStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPsychomotorStudent extends EditRecord
{
    protected static string $resource = PsychomotorStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
