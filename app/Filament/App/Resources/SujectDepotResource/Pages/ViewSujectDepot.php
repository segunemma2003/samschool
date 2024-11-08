<?php

namespace App\Filament\App\Resources\SujectDepotResource\Pages;

use App\Filament\App\Resources\SujectDepotResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSujectDepot extends ViewRecord
{
    protected static string $resource = SujectDepotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
