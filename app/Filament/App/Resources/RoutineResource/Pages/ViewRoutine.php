<?php

namespace App\Filament\App\Resources\RoutineResource\Pages;

use App\Filament\App\Resources\RoutineResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRoutine extends ViewRecord
{
    protected static string $resource = RoutineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
