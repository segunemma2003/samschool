<?php

namespace App\Filament\Hostel\Resources\ParentVisitRequestResource\Pages;

use App\Filament\Hostel\Resources\ParentVisitRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParentVisitRequests extends ListRecords
{
    protected static string $resource = ParentVisitRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
