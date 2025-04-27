<?php

namespace App\Filament\Finance\Resources\SchoolPaymentResource\Pages;

use App\Filament\Finance\Resources\SchoolPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolPayments extends ListRecords
{
    protected static string $resource = SchoolPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
