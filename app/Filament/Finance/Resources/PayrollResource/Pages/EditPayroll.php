<?php

namespace App\Filament\Finance\Resources\PayrollResource\Pages;

use App\Filament\Finance\Resources\PayrollResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayroll extends EditRecord
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
