<?php

namespace App\Filament\App\Resources\ResultSectionTypeResource\Pages;

use App\Filament\App\Resources\ResultSectionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewResultSectionType extends ViewRecord
{
    protected static string $resource = ResultSectionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
