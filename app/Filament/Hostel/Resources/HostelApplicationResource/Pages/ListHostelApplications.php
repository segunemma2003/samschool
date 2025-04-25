<?php

namespace App\Filament\Hostel\Resources\HostelApplicationResource\Pages;

use App\Filament\Hostel\Resources\HostelApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHostelApplications extends ListRecords
{
    protected static string $resource = HostelApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
