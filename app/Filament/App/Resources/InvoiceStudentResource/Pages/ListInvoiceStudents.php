<?php

namespace App\Filament\App\Resources\InvoiceStudentResource\Pages;

use App\Filament\App\Resources\InvoiceStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoiceStudents extends ListRecords
{
    protected static string $resource = InvoiceStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
