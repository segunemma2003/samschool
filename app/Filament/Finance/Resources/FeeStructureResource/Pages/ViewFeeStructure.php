<?php

namespace App\Filament\Finance\Resources\FeeStructureResource\Pages;

use App\Filament\Finance\Resources\FeeStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFeeStructure extends ViewRecord
{
    protected static string $resource = FeeStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
