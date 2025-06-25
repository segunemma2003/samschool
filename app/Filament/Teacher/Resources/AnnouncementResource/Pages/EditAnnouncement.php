<?php

namespace App\Filament\Teacher\Resources\AnnouncementResource\Pages;

use App\Filament\Teacher\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class EditAnnouncement extends EditRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Preview')
                ->icon('heroicon-m-eye')
                ->color('info'),

            Actions\DeleteAction::make()
                ->visible(fn (Model $record): bool => $record->from_id === Auth::id())
                ->requiresConfirmation(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        Notification::make()
            ->title('Announcement Updated!')
            ->body('Your changes have been saved successfully.')
            ->success()
            ->send();

        return $record;
    }

    protected function authorizeAccess(): void
    {
        $record = $this->getRecord();

        if ($record->from_id !== Auth::id() && Auth::user()->user_type !== 'admin') {
            abort(403, 'You can only edit your own announcements.');
        }
    }
}
