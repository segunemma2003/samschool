<?php

namespace App\Filament\Library\Resources\LibraryBookResource\Pages;

use App\Filament\Library\Resources\LibraryBookResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLibraryBook extends EditRecord
{
    protected static string $resource = LibraryBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
