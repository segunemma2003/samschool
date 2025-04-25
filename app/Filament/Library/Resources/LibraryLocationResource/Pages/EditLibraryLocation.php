<?php

namespace App\Filament\Library\Resources\LibraryLocationResource\Pages;

use App\Filament\Library\Resources\LibraryLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLibraryLocation extends EditRecord
{
    protected static string $resource = LibraryLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
