<?php

namespace App\Filament\Hostel\Resources\HostelFloorResource\Pages;

use App\Filament\Hostel\Resources\HostelFloorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHostelFloor extends EditRecord
{
    protected static string $resource = HostelFloorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
