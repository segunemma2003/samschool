<?php

namespace App\Filament\Hostel\Resources\HostelRoomResource\Pages;

use App\Filament\Hostel\Resources\HostelRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHostelRoom extends EditRecord
{
    protected static string $resource = HostelRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
