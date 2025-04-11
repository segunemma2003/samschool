<?php

namespace App\Filament\Ourstudent\Resources\InvoiceStudentResource\Pages;

use App\Filament\Ourstudent\Resources\InvoiceStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoiceStudent extends EditRecord
{
    protected static string $resource = InvoiceStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\ViewAction::make(),
            // Actions\DeleteAction::make(),
        ];
    }
}
