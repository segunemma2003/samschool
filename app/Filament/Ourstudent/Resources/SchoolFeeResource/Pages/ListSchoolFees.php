<?php

namespace App\Filament\Ourstudent\Resources\SchoolFeeResource\Pages;

use App\Filament\Ourstudent\Resources\SchoolFeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolFees extends ListRecords
{
    protected static string $resource = SchoolFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
