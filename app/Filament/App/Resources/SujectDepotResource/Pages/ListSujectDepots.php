<?php

namespace App\Filament\App\Resources\SujectDepotResource\Pages;

use App\Filament\App\Resources\SujectDepotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSujectDepots extends ListRecords
{
    protected static string $resource = SujectDepotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
