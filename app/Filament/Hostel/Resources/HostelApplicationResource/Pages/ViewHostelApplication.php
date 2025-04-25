<?php

namespace App\Filament\Hostel\Resources\HostelApplicationResource\Pages;

use App\Filament\Hostel\Resources\HostelApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHostelApplication extends ViewRecord
{
    protected static string $resource = HostelApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
