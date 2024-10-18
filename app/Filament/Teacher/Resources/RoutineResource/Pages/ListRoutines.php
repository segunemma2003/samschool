<?php

namespace App\Filament\Teacher\Resources\RoutineResource\Pages;

use App\Filament\Teacher\Resources\RoutineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoutines extends ListRecords
{
    protected static string $resource = RoutineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
