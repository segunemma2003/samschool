<?php

namespace App\Filament\Library\Resources\OnlineLibraryMaterialResource\Pages;

use App\Filament\Library\Resources\OnlineLibraryMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOnlineLibraryMaterial extends ViewRecord
{
    protected static string $resource = OnlineLibraryMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
