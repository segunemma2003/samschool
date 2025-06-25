<?php

namespace App\Filament\Teacher\Resources\AnnouncementResource\Pages;

use App\Filament\Teacher\Resources\AnnouncementResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['from_id'] = Auth::id();

        // Set defaults for new columns if they exist
        if (Schema::hasColumn('announcements', 'status') && !isset($data['status'])) {
            $data['status'] = 'published';
        }

        if (Schema::hasColumn('announcements', 'priority') && !isset($data['priority'])) {
            $data['priority'] = 'medium';
        }

        if (Schema::hasColumn('announcements', 'views_count') && !isset($data['views_count'])) {
            $data['views_count'] = 0;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = static::getModel()::create($data);

        Notification::make()
            ->title('Announcement Created Successfully!')
            ->body('Your announcement has been posted and is now visible to the target audience.')
            ->success()
            ->send();

        return $record;
    }
}
