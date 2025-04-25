<?php

namespace App\Filament\Hostel\Resources\HostelMaintenanceRequestResource\Pages;

use App\Filament\Hostel\Resources\HostelMaintenanceRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHostelMaintenanceRequest extends EditRecord
{
    protected static string $resource = HostelMaintenanceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
