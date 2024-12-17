<?php

namespace App\Filament\App\Resources\SchoolInformationResource\Pages;

use App\Filament\App\Resources\SchoolInformationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolInformation extends ListRecords
{
    protected static string $resource = SchoolInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
