<?php

namespace App\Filament\Hostel\Resources\HostelMaintenanceRequestResource\Pages;

use App\Filament\Hostel\Resources\HostelMaintenanceRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHostelMaintenanceRequests extends ListRecords
{
    protected static string $resource = HostelMaintenanceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
