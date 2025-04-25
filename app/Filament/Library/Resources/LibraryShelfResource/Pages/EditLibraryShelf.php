<?php

namespace App\Filament\Library\Resources\LibraryShelfResource\Pages;

use App\Filament\Library\Resources\LibraryShelfResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLibraryShelf extends EditRecord
{
    protected static string $resource = LibraryShelfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
