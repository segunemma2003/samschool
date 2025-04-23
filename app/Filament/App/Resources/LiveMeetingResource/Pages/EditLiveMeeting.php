<?php

namespace App\Filament\App\Resources\LiveMeetingResource\Pages;

use App\Filament\App\Resources\LiveMeetingResource;
use App\Services\MeetingService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EditLiveMeeting extends EditRecord
{
    protected static string $resource = LiveMeetingResource::class;


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $originalPlatform = $record->meeting_platform;
        $newPlatform = $data['meeting_platform'];

        // If the platform has changed or there's no URL, generate a new meeting link
        if ($originalPlatform !== $newPlatform || empty($record->url)) {
            $meetingService = app(MeetingService::class);
            $startDateTime = Carbon::parse($data['date_of_meeting'] . ' ' . $data['time_of_meeting']);

            try {
                $url = $meetingService->generateMeetingLink(
                    $data['meeting_platform'],
                    $data['title'],
                    $data['purpose'],
                    $startDateTime,
                    Auth::user()
                );

                if (!$url) {
                    Notification::make()
                        ->danger()
                        ->title('Failed to update meeting')
                        ->body('Unable to generate new meeting link. Please try again.')
                        ->send();

                    $this->halt();
                }

                // Update the URL in the data
                $data['url'] = $url;

            } catch (\Exception $e) {
                Notification::make()
                    ->danger()
                    ->title('Failed to update meeting')
                    ->body($e->getMessage())
                    ->send();

                $this->halt();
            }
        }

        // Update the record with all data including the potentially new URL
        $record->update($data);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
