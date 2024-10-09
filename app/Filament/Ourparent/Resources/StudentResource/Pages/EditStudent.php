<?php

namespace App\Filament\Ourparent\Resources\StudentResource\Pages;

use App\Filament\Ourparent\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
