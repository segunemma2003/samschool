<?php

namespace App\Filament\Finance\Resources\SalaryStructureResource\Pages;

use App\Filament\Finance\Resources\SalaryStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSalaryStructure extends ViewRecord
{
    protected static string $resource = SalaryStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
