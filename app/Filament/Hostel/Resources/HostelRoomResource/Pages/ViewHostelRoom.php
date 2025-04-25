<?php

namespace App\Filament\Hostel\Resources\HostelRoomResource\Pages;

use App\Filament\Hostel\Resources\HostelRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHostelRoom extends ViewRecord
{
    protected static string $resource = HostelRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
