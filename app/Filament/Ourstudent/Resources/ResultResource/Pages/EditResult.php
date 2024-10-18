<?php

namespace App\Filament\Ourstudent\Resources\ResultResource\Pages;

use App\Filament\Ourstudent\Resources\ResultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResult extends EditRecord
{
    protected static string $resource = ResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
