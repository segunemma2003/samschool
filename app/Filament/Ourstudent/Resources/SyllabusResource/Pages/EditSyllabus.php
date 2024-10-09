<?php

namespace App\Filament\Ourstudent\Resources\SyllabusResource\Pages;

use App\Filament\Ourstudent\Resources\SyllabusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSyllabus extends EditRecord
{
    protected static string $resource = SyllabusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
