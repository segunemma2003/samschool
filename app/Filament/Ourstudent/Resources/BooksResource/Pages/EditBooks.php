<?php

namespace App\Filament\Ourstudent\Resources\BooksResource\Pages;

use App\Filament\Ourstudent\Resources\BooksResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBooks extends EditRecord
{
    protected static string $resource = BooksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
