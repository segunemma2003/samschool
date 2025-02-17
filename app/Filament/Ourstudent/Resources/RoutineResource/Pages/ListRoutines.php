<?php

namespace App\Filament\Ourstudent\Resources\RoutineResource\Pages;

use App\Filament\Ourstudent\Resources\RoutineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoutines extends ListRecords
{
    protected static string $resource = RoutineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
