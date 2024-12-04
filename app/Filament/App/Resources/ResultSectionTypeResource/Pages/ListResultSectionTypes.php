<?php

namespace App\Filament\App\Resources\ResultSectionTypeResource\Pages;

use App\Filament\App\Resources\ResultSectionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResultSectionTypes extends ListRecords
{
    protected static string $resource = ResultSectionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
