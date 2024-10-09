<?php

namespace App\Filament\Ourstudent\Resources\RoutineResource\Pages;

use App\Filament\Ourstudent\Resources\RoutineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoutine extends EditRecord
{
    protected static string $resource = RoutineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
