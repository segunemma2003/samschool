<?php

namespace App\Filament\Teacher\Resources\ResultResource\Pages;

use App\Filament\Teacher\Resources\ResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResults extends ListRecords
{
    protected static string $resource = ResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected static string $view = "filament.teacher.resources.result.view";
}
