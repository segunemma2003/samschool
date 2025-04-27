<?php

namespace App\Filament\Finance\Resources\ClassFeeResource\Pages;

use App\Filament\Finance\Resources\ClassFeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassFee extends EditRecord
{
    protected static string $resource = ClassFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
