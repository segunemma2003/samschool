<?php

namespace App\Filament\Hostel\Resources\HostelInventoryResource\Pages;

use App\Filament\Hostel\Resources\HostelInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHostelInventory extends EditRecord
{
    protected static string $resource = HostelInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
