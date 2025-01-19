<?php

namespace App\Filament\App\Resources\DownloadStatusResource\Pages;

use App\Filament\App\Resources\DownloadStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDownloadStatuses extends ListRecords
{
    protected static string $resource = DownloadStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
