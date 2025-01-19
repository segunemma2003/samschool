<?php

namespace App\Filament\App\Resources\DownloadStatusResource\Pages;

use App\Filament\App\Resources\DownloadStatusResource;
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
