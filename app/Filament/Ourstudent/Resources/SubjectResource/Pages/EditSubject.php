<?php

namespace App\Filament\Ourstudent\Resources\SubjectResource\Pages;

use App\Filament\Ourstudent\Resources\SubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubject extends EditRecord
{
    protected static string $resource = SubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
