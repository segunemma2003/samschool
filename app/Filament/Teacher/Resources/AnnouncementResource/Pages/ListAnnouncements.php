<?php

namespace App\Filament\Teacher\Resources\AnnouncementResource\Pages;

use App\Filament\Teacher\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListAnnouncements extends ListRecords
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Announcement')
                ->icon('heroicon-m-plus')
                ->color('primary'),
        ];
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}
