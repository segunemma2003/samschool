<?php

namespace App\Filament\Ourparent\Resources\InvoiceStudentResource\Pages;

use App\Filament\Ourparent\Resources\InvoiceStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoiceStudent extends ViewRecord
{
    protected static string $resource = InvoiceStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }
}
