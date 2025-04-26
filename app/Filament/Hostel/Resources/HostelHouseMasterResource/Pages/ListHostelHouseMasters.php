<?php

namespace App\Filament\Hostel\Resources\HostelHouseMasterResource\Pages;

use App\Filament\Hostel\Resources\HostelHouseMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHostelHouseMasters extends ListRecords
{
    protected static string $resource = HostelHouseMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
