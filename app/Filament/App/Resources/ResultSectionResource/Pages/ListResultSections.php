<?php

namespace App\Filament\App\Resources\ResultSectionResource\Pages;

use App\Filament\App\Resources\ResultSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResultSections extends ListRecords
{
    protected static string $resource = ResultSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
