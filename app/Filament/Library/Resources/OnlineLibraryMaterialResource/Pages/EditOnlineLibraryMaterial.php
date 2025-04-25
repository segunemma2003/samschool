<?php

namespace App\Filament\Library\Resources\OnlineLibraryMaterialResource\Pages;

use App\Filament\Library\Resources\OnlineLibraryMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOnlineLibraryMaterial extends EditRecord
{
    protected static string $resource = OnlineLibraryMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
