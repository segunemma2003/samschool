<?php

namespace App\Filament\Ourstudent\Resources\InvoiceStudentResource\Pages;

use App\Filament\Ourstudent\Resources\InvoiceStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoiceStudents extends ListRecords
{
    protected static string $resource = InvoiceStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
