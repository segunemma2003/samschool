<?php

namespace App\Filament\Finance\Resources\SchoolInvoiceResource\Pages;

use App\Filament\Finance\Resources\SchoolInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolInvoices extends ListRecords
{
    protected static string $resource = SchoolInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
