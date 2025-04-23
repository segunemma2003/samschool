<?php

namespace App\Filament\App\Resources\LiveMeetingResource\Pages;

use App\Filament\App\Resources\LiveMeetingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLiveMeetings extends ListRecords
{
    protected static string $resource = LiveMeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
