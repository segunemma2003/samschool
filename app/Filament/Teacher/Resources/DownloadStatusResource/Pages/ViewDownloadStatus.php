<?php

namespace App\Filament\Teacher\Resources\DownloadStatusResource\Pages;

use App\Filament\Teacher\Resources\DownloadStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDownloadStatus extends ViewRecord
{
    protected static string $resource = DownloadStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
