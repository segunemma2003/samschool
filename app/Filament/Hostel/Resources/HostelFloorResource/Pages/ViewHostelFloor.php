<?php

namespace App\Filament\Hostel\Resources\HostelFloorResource\Pages;

use App\Filament\Hostel\Resources\HostelFloorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHostelFloor extends ViewRecord
{
    protected static string $resource = HostelFloorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
