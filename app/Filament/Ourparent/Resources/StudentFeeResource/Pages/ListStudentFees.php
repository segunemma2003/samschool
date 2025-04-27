<?php

namespace App\Filament\Ourparent\Resources\StudentFeeResource\Pages;

use App\Filament\Ourparent\Resources\StudentFeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentFees extends ListRecords
{
    protected static string $resource = StudentFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
