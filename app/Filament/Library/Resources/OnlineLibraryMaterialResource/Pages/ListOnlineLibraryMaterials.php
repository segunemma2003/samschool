<?php

namespace App\Filament\Library\Resources\OnlineLibraryMaterialResource\Pages;

use App\Filament\Library\Resources\OnlineLibraryMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOnlineLibraryMaterials extends ListRecords
{
    protected static string $resource = OnlineLibraryMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
