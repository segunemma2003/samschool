<?php

namespace App\Filament\Hostel\Resources\HostelInventoryResource\Pages;

use App\Filament\Hostel\Resources\HostelInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHostelInventory extends ViewRecord
{
    protected static string $resource = HostelInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
