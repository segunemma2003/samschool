<?php

namespace App\Filament\Hostel\Resources\HostelMaintenanceRequestResource\Pages;

use App\Filament\Hostel\Resources\HostelMaintenanceRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHostelMaintenanceRequest extends ViewRecord
{
    protected static string $resource = HostelMaintenanceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
