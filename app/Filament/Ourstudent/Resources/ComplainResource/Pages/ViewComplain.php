<?php

namespace App\Filament\Ourstudent\Resources\ComplainResource\Pages;

use App\Filament\Ourstudent\Resources\ComplainResource;
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
