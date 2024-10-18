<?php

namespace App\Filament\Ourstudent\Resources\LibraryResource\Pages;

use App\Filament\Ourstudent\Resources\LibraryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLibrary extends EditRecord
{
    protected static string $resource = LibraryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
