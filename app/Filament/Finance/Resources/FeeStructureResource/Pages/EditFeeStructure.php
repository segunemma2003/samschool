<?php

namespace App\Filament\Finance\Resources\FeeStructureResource\Pages;

use App\Filament\Finance\Resources\FeeStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeeStructure extends EditRecord
{
    protected static string $resource = FeeStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
