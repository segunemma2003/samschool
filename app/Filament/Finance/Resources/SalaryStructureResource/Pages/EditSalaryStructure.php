<?php

namespace App\Filament\Finance\Resources\SalaryStructureResource\Pages;

use App\Filament\Finance\Resources\SalaryStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalaryStructure extends EditRecord
{
    protected static string $resource = SalaryStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
