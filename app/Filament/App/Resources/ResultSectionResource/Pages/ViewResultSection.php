<?php

namespace App\Filament\App\Resources\ResultSectionResource\Pages;

use App\Filament\App\Resources\ResultSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewResultSection extends ViewRecord
{
    protected static string $resource = ResultSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
