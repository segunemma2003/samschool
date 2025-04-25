<?php

namespace App\Filament\Hostel\Resources\HostelInventoryResource\Pages;

use App\Filament\Hostel\Resources\HostelInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHostelInventories extends ListRecords
{
    protected static string $resource = HostelInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
