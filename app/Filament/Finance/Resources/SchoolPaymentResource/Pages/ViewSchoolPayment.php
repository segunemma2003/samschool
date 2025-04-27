<?php

namespace App\Filament\Finance\Resources\SchoolPaymentResource\Pages;

use App\Filament\Finance\Resources\SchoolPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSchoolPayment extends ViewRecord
{
    protected static string $resource = SchoolPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
