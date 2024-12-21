<?php

namespace App\Filament\Teacher\Resources\DownloadStatusResource\Pages;

use App\Filament\Teacher\Resources\DownloadStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDownloadStatus extends EditRecord
{
    protected static string $resource = DownloadStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
