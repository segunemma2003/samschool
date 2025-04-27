<?php

namespace App\Filament\Finance\Resources\PayrollResource\Pages;

use App\Filament\Finance\Resources\PayrollResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPayroll extends ViewRecord
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
