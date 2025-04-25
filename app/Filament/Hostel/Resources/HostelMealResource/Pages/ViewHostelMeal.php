<?php

namespace App\Filament\Hostel\Resources\HostelMealResource\Pages;

use App\Filament\Hostel\Resources\HostelMealResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHostelMeal extends ViewRecord
{
    protected static string $resource = HostelMealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
