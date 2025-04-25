<?php

namespace App\Filament\Hostel\Resources\HostelMealResource\Pages;

use App\Filament\Hostel\Resources\HostelMealResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHostelMeals extends ListRecords
{
    protected static string $resource = HostelMealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
