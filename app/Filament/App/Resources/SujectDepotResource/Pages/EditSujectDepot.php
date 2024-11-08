<?php

namespace App\Filament\App\Resources\SujectDepotResource\Pages;

use App\Filament\App\Resources\SujectDepotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSujectDepot extends EditRecord
{
    protected static string $resource = SujectDepotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
