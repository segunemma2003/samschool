<?php

namespace App\Filament\Hostel\Resources\HostelBuildingResource\Pages;

use App\Filament\Hostel\Resources\HostelBuildingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHostelBuildings extends ListRecords
{
    protected static string $resource = HostelBuildingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
