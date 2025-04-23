<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Client as Google_Client;
use Google\Service\Calendar as Google_Service_Calendar;
use Google\Service\Calendar\Event as Google_Service_Calendar_Event;
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Graph\Generated\Models\OnlineMeeting; // Add this if you need it
use Microsoft\Graph\Model; // Add this if you need it
use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Exception;

class MeetingService
{
    /**
     * Generate a meeting link based on the chosen platform
     */
    public function generateMeetingLink(
        string $platform,
        string $title,
        string $description,
        Carbon $startDateTime,
        User $user,
        int $durationMinutes = 60
    ): string {
        return match($platform) {
            'google_meet' => $this->createGoogleMeetMeeting($title, $description, $startDateTime, $user, $durationMinutes),
            'ms_teams' => $this->createMSTeamsMeeting($title, $description, $startDateTime, $user, $durationMinutes),
            'zoom' => $this->createZoomMeeting($title, $description, $startDateTime, $user, $durationMinutes),
            default => throw new Exception("Unsupported platform: {$platform}"),
        };
    }

    /**
     * Create a Google Meet meeting
     */
    private function createGoogleMeetMeeting(
        string $title,
        string $description,
        Carbon $startDateTime,
        User $user,
        int $durationMinutes
    ): string {
        try {
            $client = $this->getGoogleClient($user);
            $service = new Google_Service_Calendar($client);

            $endDateTime = (clone $startDateTime)->addMinutes($durationMinutes);

            $event = new Google_Service_Calendar_Event([
                'summary' => $title,
                'description' => $description,
                'start' => [
                    'dateTime' => $startDateTime->toIso8601String(),
                    'timeZone' => config('app.timezone'),
                ],
                'end' => [
                    'dateTime' => $endDateTime->toIso8601String(),
                    'timeZone' => config('app.timezone'),
                ],
                'conferenceData' => [
                    'createRequest' => [
                        'requestId' => uniqid('meet-', true),
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    ],
                ],
            ]);

            $event = $service->events->insert('primary', $event, [
                'conferenceDataVersion' => 1,
                'sendUpdates' => 'all',
            ]);

            return $event->getHangoutLink();

        } catch (Exception $e) {
            Log::error('Google Meet creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user' => $user->id,
            ]);
            throw new Exception("Google Meet creation failed: " . $e->getMessage());
        }
    }

    /**
     * Create a Microsoft Teams meeting
     */
    private function createMSTeamsMeeting(
        string $title,
        string $description,
        Carbon $startDateTime,
        User $user,
        int $durationMinutes
    ): string {
        return "https://meet.google.com";
        // try {

        //     $graphServiceClient = new GraphServiceClient($tokenRequestContext, $scopes);

        //     $requestBody = new OnlineMeeting();
        //     $endDateTime = (clone $startDateTime)->addMinutes($durationMinutes);
        //     $requestBody->setStartDateTime($startDateTime);
        //     $requestBody->setEndDateTime($endDateTime);
        //     $requestBody->setSubject($title);

        //     $meeting = $graphServiceClient->me()->onlineMeetings()->post($requestBody)->wait();


        //     return $meeting['joinWebUrl'] ??
        //            throw new Exception("No join URL in Teams response");

        // } catch (Exception $e) {
        //     Log::error('Teams meeting creation failed', [
        //         'error' => $e->getMessage(),
        //         'trace' => $e->getTraceAsString(),
        //         'user' => $user->id,
        //     ]);
        //     throw new Exception("Teams meeting creation failed: " . $e->getMessage());
        // }
    }

    /**
     * Create a Zoom meeting
     */
    private function createZoomMeeting(
        string $title,
        string $description,
        Carbon $startDateTime,
        User $user,
        int $durationMinutes
    ): string {
        try {
            $token = $this->getZoomAccessToken($user);
            $zoomUserId = $user->zoom_user_id ?? 'me';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post("https://api.zoom.us/v2/users/{$zoomUserId}/meetings", [
                'topic' => $title,
                'type' => 2, // Scheduled meeting
                'start_time' => $startDateTime->toIso8601String(),
                'duration' => $durationMinutes,
                'timezone' => config('app.timezone'),
                'agenda' => $description,
                'settings' => [
                    'host_video' => true,
                    'participant_video' => true,
                    'join_before_host' => false,
                    'mute_upon_entry' => false,
                    'waiting_room' => false,
                    'auto_recording' => 'none',
                ],
            ]);

            if ($response->successful()) {
                return $response->json()['join_url'];
            }

            $error = $response->json();
            Log::error('Zoom API error', [
                'status' => $response->status(),
                'error' => $error,
                'user' => $user->id,
            ]);
            throw new Exception($error['message'] ?? 'Zoom API request failed');

        } catch (Exception $e) {
            Log::error('Zoom meeting creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user' => $user->id,
            ]);
            throw new Exception("Zoom meeting creation failed: " . $e->getMessage());
        }
    }

    /**
     * Get authenticated Google Client
     */
    private function getGoogleClient(User $user): Google_Client
    {
        $client = new Google_Client();
        $client->setApplicationName(config('services.google.app_name'));
        $client->setScopes([Google_Service_Calendar::CALENDAR]);

        if (config('services.google.auth_type') === 'service_account') {
            $client->setAuthConfig(storage_path('app/google-service-account.json'));
            $client->setSubject($user->email);
        } else {
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setAccessToken($this->getGoogleAccessToken($user));

            if ($client->isAccessTokenExpired()) {
                $this->refreshGoogleToken($client, $user);
            }
        }

        return $client;
    }

    /**
     * Get Microsoft access token with refresh handling
     */
    private function getMicrosoftAccessToken(User $user): string
    {
        if ($user->ms_access_token && $user->ms_token_expires_at > now()->addMinutes(5)) {
            return $user->ms_access_token;
        }

        if (!$user->ms_refresh_token) {
            throw new Exception('Microsoft refresh token missing');
        }

        $response = Http::asForm()->post(
            'https://login.microsoftonline.com/' . config('services.microsoft.tenant_id') . '/oauth2/v2.0/token',
            [
                'client_id' => config('services.microsoft.client_id'),
                'client_secret' => config('services.microsoft.client_secret'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $user->ms_refresh_token,
                'scope' => 'offline_access OnlineMeetings.ReadWrite Calendars.ReadWrite',
            ]
        );

        if (!$response->successful()) {
            throw new Exception('Microsoft token refresh failed');
        }

        $data = $response->json();
        $user->update([
            'ms_access_token' => $data['access_token'],
            'ms_refresh_token' => $data['refresh_token'] ?? $user->ms_refresh_token,
            'ms_token_expires_at' => now()->addSeconds($data['expires_in']),
        ]);

        return $data['access_token'];
    }

    /**
     * Get Zoom access token
     */
    private function getZoomAccessToken(User $user): string
    {
        if (config('services.zoom.auth_type') === 'jwt') {
            return $this->generateZoomJwtToken(
                config('services.zoom.api_key'),
                config('services.zoom.api_secret')
            );
        }

        if ($user->zoom_access_token && $user->zoom_token_expires_at > now()->addMinutes(5)) {
            return $user->zoom_access_token;
        }

        if (!$user->zoom_refresh_token) {
            throw new Exception('Zoom refresh token missing');
        }

        $response = Http::asForm()
            ->withBasicAuth(
                config('services.zoom.client_id'),
                config('services.zoom.client_secret')
            )
            ->post('https://zoom.us/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $user->zoom_refresh_token,
            ]);

        if (!$response->successful()) {
            throw new Exception('Zoom token refresh failed');
        }

        $data = $response->json();
        $user->update([
            'zoom_access_token' => $data['access_token'],
            'zoom_refresh_token' => $data['refresh_token'] ?? $user->zoom_refresh_token,
            'zoom_token_expires_at' => now()->addSeconds($data['expires_in']),
        ]);

        return $data['access_token'];
    }

    /**
     * Generate JWT token for Zoom (legacy)
     */
    private function generateZoomJwtToken(string $key, string $secret): string
    {
        $payload = [
            'iss' => $key,
            'exp' => time() + 3600,
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    /**
     * Refresh Google token if expired
     */
    private function refreshGoogleToken(Google_Client $client, User $user): void
    {
        $refreshToken = $user->google_refresh_token;
        if (!$refreshToken) {
            throw new Exception('Google refresh token missing');
        }

        $client->fetchAccessTokenWithRefreshToken($refreshToken);
        $token = $client->getAccessToken();

        $user->update([
            'google_access_token' => $token['access_token'],
            'google_token_expires_at' => now()->addSeconds($token['expires_in']),
            'google_refresh_token' => $token['refresh_token'] ?? $refreshToken,
        ]);
    }

    /**
     * Get Google access token from user
     */
    private function getGoogleAccessToken(User $user): array
    {
        if (!$user->google_access_token) {
            throw new Exception('Google access token missing');
        }

        return [
            'access_token' => $user->google_access_token,
            'refresh_token' => $user->google_refresh_token,
            'expires_in' => $user->google_token_expires_at->diffInSeconds(now()),
            'created' => $user->google_token_expires_at->subSeconds($user->google_token_expires_at->diffInSeconds(now()))->timestamp,
        ];
    }
}
