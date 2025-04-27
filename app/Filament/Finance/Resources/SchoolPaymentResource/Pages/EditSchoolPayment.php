<?php

namespace App\Filament\Finance\Resources\SchoolPaymentResource\Pages;

use App\Filament\Finance\Resources\SchoolPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolPayment extends EditRecord
{
    protected static string $resource = SchoolPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
