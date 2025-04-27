<?php

namespace App\Filament\Finance\Resources\SalaryStructureResource\Pages;

use App\Filament\Finance\Resources\SalaryStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalaryStructures extends ListRecords
{
    protected static string $resource = SalaryStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
