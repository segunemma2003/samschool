<?php

namespace App\Filament\Hostel\Resources\HostelHouseMasterResource\Pages;

use App\Filament\Hostel\Resources\HostelHouseMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHostelHouseMaster extends EditRecord
{
    protected static string $resource = HostelHouseMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
