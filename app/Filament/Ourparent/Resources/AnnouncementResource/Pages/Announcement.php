<?php

namespace App\Filament\Ourparent\Resources\AnnouncementResource\Pages;

use App\Filament\Ourparent\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class Announcement extends ViewRecord
{
    protected static string $resource = AnnouncementResource::class;
    protected static ?string $title = '';
    protected ?string $heading = '';
    protected static string $view = 'filament.parent.resources.announcement.pages.announcement';
}
