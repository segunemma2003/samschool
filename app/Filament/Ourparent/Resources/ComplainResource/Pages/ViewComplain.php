<?php

namespace App\Filament\Ourparent\Resources\ComplainResource\Pages;

use App\Filament\Ourparent\Resources\ComplainResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewComplain extends ViewRecord
{
    protected static string $resource = ComplainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
