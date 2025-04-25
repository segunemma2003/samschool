<?php

namespace App\Filament\Hostel\Resources\HostelBuildingResource\Pages;

use App\Filament\Hostel\Resources\HostelBuildingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHostelBuilding extends EditRecord
{
    protected static string $resource = HostelBuildingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
