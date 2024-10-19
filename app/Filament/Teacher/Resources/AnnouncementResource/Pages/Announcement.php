<?php

namespace App\Filament\Teacher\Resources\AnnouncementResource\Pages;

use App\Filament\Teacher\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class Announcement extends ViewRecord
{
    protected static string $resource = AnnouncementResource::class;
    protected static ?string $title = '';
    protected ?string $heading = '';
    protected static string $view = 'filament.teacher.resources.announcement.pages.announcement';

}
