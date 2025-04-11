<?php
namespace App\Services;

use Carbon\Carbon;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use GuzzleHttp\Client;

class TeamsService
{
    // protected $graph;

    // /**
    //  * Constructor - initialize the Graph API client
    //  */
    // public function __construct()
    // {
    //     $this->graph = new Graph();
    //     $this->graph->setAccessToken($this->getAccessToken());
    // }

    // /**
    //  * Get Microsoft Graph API access token
    //  *
    //  * @return string
    //  */
    // protected function getAccessToken(): string
    // {
    //     // You'll need to implement authentication logic here
    //     // This typically involves requesting a token from Azure AD
    //     // using client credentials, authorization code, or other OAuth flows

    //     $tenantId = config('services.microsoft.tenant_id');
    //     $clientId = config('services.microsoft.client_id');
    //     $clientSecret = config('services.microsoft.client_secret');

    //     $guzzle = new Client();
    //     $url = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";

    //     $response = $guzzle->post($url, [
    //         'form_params' => [
    //             'client_id' => $clientId,
    //             'client_secret' => $clientSecret,
    //             'scope' => 'https://graph.microsoft.com/.default',
    //             'grant_type' => 'client_credentials',
    //         ],
    //     ]);

    //     $token = json_decode($response->getBody()->getContents(), true);
    //     return $token['access_token'];
    // }

    // /**
    //  * Create a new Microsoft Teams meeting
    //  *
    //  * @param string $title Meeting title
    //  * @param string $description Meeting description
    //  * @param Carbon $startDateTime Start time
    //  * @param Carbon $endDateTime End time
    //  * @param array $attendees Array of email addresses
    //  * @return array Meeting details including join URL
    //  */
    // public function createMeeting(
    //     string $title,
    //     string $description,
    //     Carbon $startDateTime,
    //     Carbon $endDateTime,
    //     array $attendees = []
    // ): array {
    //     // Format attendees for Microsoft Graph API
    //     $formattedAttendees = [];
    //     foreach ($attendees as $email) {
    //         $formattedAttendees[] = [
    //             'emailAddress' => [
    //                 'address' => $email
    //             ],
    //             'type' => 'required'
    //         ];
    //     }

    //     // Format start and end time
    //     $startTime = $startDateTime->format('Y-m-d\TH:i:s');
    //     $endTime = $endDateTime->format('Y-m-d\TH:i:s');

    //     // Define the event with online meeting
    //     $eventData = [
    //         'subject' => $title,
    //         'body' => [
    //             'contentType' => 'text',
    //             'content' => $description
    //         ],
    //         'start' => [
    //             'dateTime' => $startTime,
    //             'timeZone' => 'UTC'
    //         ],
    //         'end' => [
    //             'dateTime' => $endTime,
    //             'timeZone' => 'UTC'
    //         ],
    //         'attendees' => $formattedAttendees,
    //         'isOnlineMeeting' => true,
    //         'onlineMeetingProvider' => 'teamsForBusiness'
    //     ];

    //     // Create the event through Microsoft Graph API
    //     $response = $this->graph->createRequest('POST', '/me/events')
    //         ->attachBody($eventData)
    //         ->setReturnType(Model\Event::class)
    //         ->execute();

    //     return [
    //         'id' => $response->getId(),
    //         'title' => $response->getSubject(),
    //         'description' => $response->getBody()->getContent(),
    //         'start_time' => $startDateTime,
    //         'end_time' => $endDateTime,
    //         'meeting_url' => $response->getOnlineMeeting()->getJoinUrl(),
    //         'provider' => 'microsoft_teams',
    //     ];
    // }

    // /**
    //  * Update an existing Microsoft Teams meeting
    //  *
    //  * @param string $eventId
    //  * @param array $attributes
    //  * @return array Updated meeting details
    //  */
    // public function updateMeeting(string $eventId, array $attributes): array
    // {
    //     $updateData = [];

    //     if (isset($attributes['title'])) {
    //         $updateData['subject'] = $attributes['title'];
    //     }

    //     if (isset($attributes['description'])) {
    //         $updateData['body'] = [
    //             'contentType' => 'text',
    //             'content' => $attributes['description']
    //         ];
    //     }

    //     if (isset($attributes['start_time'])) {
    //         $updateData['start'] = [
    //             'dateTime' => $attributes['start_time']->format('Y-m-d\TH:i:s'),
    //             'timeZone' => 'UTC'
    //         ];
    //     }

    //     if (isset($attributes['end_time'])) {
    //         $updateData['end'] = [
    //             'dateTime' => $attributes['end_time']->format('Y-m-d\TH:i:s'),
    //             'timeZone' => 'UTC'
    //         ];
    //     }

    //     if (isset($attributes['attendees'])) {
    //         $formattedAttendees = [];
    //         foreach ($attributes['attendees'] as $email) {
    //             $formattedAttendees[] = [
    //                 'emailAddress' => [
    //                     'address' => $email
    //                 ],
    //                 'type' => 'required'
    //             ];
    //         }
    //         $updateData['attendees'] = $formattedAttendees;
    //     }

    //     // Update the event through Microsoft Graph API
    //     $response = $this->graph->createRequest('PATCH', "/me/events/{$eventId}")
    //         ->attachBody($updateData)
    //         ->setReturnType(Model\Event::class)
    //         ->execute();

    //     return [
    //         'id' => $response->getId(),
    //         'title' => $response->getSubject(),
    //         'description' => $response->getBody()->getContent(),
    //         'start_time' => new Carbon($response->getStart()->getDateTime()),
    //         'end_time' => new Carbon($response->getEnd()->getDateTime()),
    //         'meeting_url' => $response->getOnlineMeeting()->getJoinUrl(),
    //         'provider' => 'microsoft_teams',
    //     ];
    // }

    // /**
    //  * Delete a Microsoft Teams meeting
    //  *
    //  * @param string $eventId
    //  * @return boolean Success status
    //  */
    // public function deleteMeeting(string $eventId): bool
    // {
    //     $this->graph->createRequest('DELETE', "/me/events/{$eventId}")
    //         ->execute();

    //     return true;
    // }
}
