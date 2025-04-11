<?php
namespace App\Services;

use Carbon\Carbon;
use Spatie\GoogleCalendar\Event;

class GoogleMeetService
{
    /**
     * Create a new Google Meet meeting
     *
     * @param string $title Meeting title
     * @param string $description Meeting description
     * @param Carbon $startDateTime Start time
     * @param Carbon $endDateTime End time
     * @param array $attendees Array of email addresses
     * @return array Meeting details including join URL
     */
    public function createMeeting(
        string $title,
        string $description,
        Carbon $startDateTime,
        Carbon $endDateTime,
        array $attendees = []
    ): array {
        $event = new Event;

        $event->name = $title;
        $event->description = $description;
        $event->startDateTime = $startDateTime;
        $event->endDateTime = $endDateTime;

        // Format attendees as required by Google Calendar API
        $formattedAttendees = [];
        foreach ($attendees as $email) {
            $formattedAttendees[] = ['email' => $email];
        }
        $event->attendees = $formattedAttendees;

        // Add Google Meet conferencing data
        $event->addConferenceData = true;
        $event->conferenceData = [
            'createRequest' => [
                'requestId' => uniqid(),
                'conferenceSolutionKey' => [
                    'type' => 'hangoutsMeet'
                ]
            ]
        ];

        // Save the event to Google Calendar
        $createdEvent = $event->save();

        // Extract meeting URL and details
        $meetLink = $createdEvent->hangoutLink ?? null;

        return [
            'id' => $createdEvent->id,
            'title' => $createdEvent->name,
            'description' => $createdEvent->description,
            'start_time' => $createdEvent->startDateTime,
            'end_time' => $createdEvent->endDateTime,
            'meeting_url' => $meetLink,
            'calendar_event_id' => $createdEvent->id,
            'provider' => 'google_meet',
        ];
    }

    /**
     * Update an existing Google Meet meeting
     *
     * @param string $eventId
     * @param array $attributes
     * @return array Updated meeting details
     */
    public function updateMeeting(string $eventId, array $attributes): array
    {
        $event = Event::find($eventId);

        if (isset($attributes['title'])) {
            $event->name = $attributes['title'];
        }

        if (isset($attributes['description'])) {
            $event->description = $attributes['description'];
        }

        if (isset($attributes['start_time'])) {
            $event->startDateTime = $attributes['start_time'];
        }

        if (isset($attributes['end_time'])) {
            $event->endDateTime = $attributes['end_time'];
        }

        if (isset($attributes['attendees'])) {
            $formattedAttendees = [];
            foreach ($attributes['attendees'] as $email) {
                $formattedAttendees[] = ['email' => $email];
            }
            $event->attendees = $formattedAttendees;
        }

        $updatedEvent = $event->save();

        return [
            'id' => $updatedEvent->id,
            'title' => $updatedEvent->name,
            'description' => $updatedEvent->description,
            'start_time' => $updatedEvent->startDateTime,
            'end_time' => $updatedEvent->endDateTime,
            'meeting_url' => $updatedEvent->hangoutLink,
            'provider' => 'google_meet',
        ];
    }

    /**
     * Delete a Google Meet meeting
     *
     * @param string $eventId
     * @return boolean Success status
     */
    public function deleteMeeting(string $eventId)
    {
        $event = Event::find($eventId);
        return $event->delete();
    }
}
