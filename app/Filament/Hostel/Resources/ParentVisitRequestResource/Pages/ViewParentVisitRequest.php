<?php

namespace App\Filament\Hostel\Resources\ParentVisitRequestResource\Pages;

use App\Filament\Hostel\Resources\ParentVisitRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewParentVisitRequest extends ViewRecord
{
    protected static string $resource = ParentVisitRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
