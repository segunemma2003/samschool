<?php

namespace App\Filament\Finance\Resources\ClassFeeResource\Pages;

use App\Filament\Finance\Resources\ClassFeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClassFee extends ViewRecord
{
    protected static string $resource = ClassFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
