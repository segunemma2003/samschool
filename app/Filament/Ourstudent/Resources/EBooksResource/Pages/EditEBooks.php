<?php

namespace App\Filament\Ourstudent\Resources\EBooksResource\Pages;

use App\Filament\Ourstudent\Resources\EBooksResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEBooks extends EditRecord
{
    protected static string $resource = EBooksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
