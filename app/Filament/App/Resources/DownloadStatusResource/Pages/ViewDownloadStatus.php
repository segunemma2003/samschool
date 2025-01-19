<?php

namespace App\Filament\App\Resources\DownloadStatusResource\Pages;

use App\Filament\App\Resources\DownloadStatusResource;
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
