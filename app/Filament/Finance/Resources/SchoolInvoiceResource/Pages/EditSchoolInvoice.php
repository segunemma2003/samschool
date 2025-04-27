<?php

namespace App\Filament\Finance\Resources\SchoolInvoiceResource\Pages;

use App\Filament\Finance\Resources\SchoolInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolInvoice extends EditRecord
{
    protected static string $resource = SchoolInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
