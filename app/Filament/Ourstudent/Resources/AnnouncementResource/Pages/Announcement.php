<?php

namespace App\Filament\Ourstudent\Resources\AnnouncementResource\Pages;

use App\Filament\Ourstudent\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class Announcement extends ViewRecord
{
    protected static string $resource = AnnouncementResource::class;
    protected static ?string $title = '';
    protected ?string $heading = '';
    protected static string $view = 'filament.ourstudent.resources.announcement.pages.announcement';
}
