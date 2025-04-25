<?php

namespace App\Filament\Hostel\Resources\HostelMealResource\Pages;

use App\Filament\Hostel\Resources\HostelMealResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHostelMeal extends EditRecord
{
    protected static string $resource = HostelMealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
