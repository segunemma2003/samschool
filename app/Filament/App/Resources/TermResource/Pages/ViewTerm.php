<?php

namespace App\Filament\App\Resources\TermResource\Pages;

use App\Filament\App\Resources\TermResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTerm extends ViewRecord
{
    protected static string $resource = TermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
