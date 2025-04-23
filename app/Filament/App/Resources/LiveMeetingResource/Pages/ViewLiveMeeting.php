<?php

namespace App\Filament\App\Resources\LiveMeetingResource\Pages;

use App\Filament\App\Resources\LiveMeetingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLiveMeeting extends ViewRecord
{
    protected static string $resource = LiveMeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
