<?php

namespace App\Filament\Teacher\Resources\SalaryTemplaeResource\Pages;

use App\Filament\Teacher\Resources\SalaryTemplaeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalaryTemplaes extends ListRecords
{
    protected static string $resource = SalaryTemplaeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
