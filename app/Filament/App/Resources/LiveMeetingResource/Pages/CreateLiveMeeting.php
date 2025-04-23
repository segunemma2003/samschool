<?php

namespace App\Filament\App\Resources\LiveMeetingResource\Pages;

use App\Filament\App\Resources\LiveMeetingResource;
use App\Services\MeetingService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CreateLiveMeeting extends CreateRecord
{
    protected static string $resource = LiveMeetingResource::class;


    protected function handleRecordCreation(array $data): Model
    {
        // Generate meeting link based on the platform
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
                    ->title('Failed to create meeting')
                    ->body('Unable to generate meeting link. Please try again.')
                    ->send();

                // Return a placeholder model - this won't be reached since halt() throws an exception
                $this->halt();

                // This line is just to satisfy the return type requirement if halt() didn't throw an exception
                return static::getModel()::make();
            }

            // Add the generated URL and user_id to the data
            $data['url'] = $url;
            $data['user_id'] = Auth::id();

            // Create the record with the URL included
            return static::getModel()::create($data);

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Failed to create meeting')
                ->body($e->getMessage())
                ->send();

            // Return a placeholder model - this won't be reached since halt() throws an exception
            $this->halt();

            // This line is just to satisfy the return type requirement if halt() didn't throw an exception
            return static::getModel()::make();
        }
    }

}
