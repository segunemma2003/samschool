<?php

namespace App\Filament\Hostel\Resources\HostelVisitorResource\Pages;

use App\Filament\Hostel\Resources\HostelVisitorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHostelVisitor extends ViewRecord
{
    protected static string $resource = HostelVisitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
