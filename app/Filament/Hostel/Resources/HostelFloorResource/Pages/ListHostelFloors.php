<?php

namespace App\Filament\Hostel\Resources\HostelFloorResource\Pages;

use App\Filament\Hostel\Resources\HostelFloorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHostelFloors extends ListRecords
{
    protected static string $resource = HostelFloorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
