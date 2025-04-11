<?php
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;

class ZoomService
{
    protected $apiKey;
    protected $apiSecret;
    protected $baseUrl = 'https://api.zoom.us/v2';

    /**
     * Constructor - initialize the Zoom API client
     */
    public function __construct()
    {
        $this->apiKey = config('services.zoom.api_key');
        $this->apiSecret = config('services.zoom.api_secret');
    }

    /**
     * Generate a JWT token for Zoom API authentication
     *
     * @return string
     */
    protected function generateJWT(): string
    {
        $payload = [
            'iss' => $this->apiKey,
            'exp' => time() + 60, // Token expires in 1 minute (Zoom API requirement)
        ];

        return JWT::encode($payload, $this->apiSecret, 'HS256');
    }

    /**
     * Create a new Zoom meeting
     *
     * @param string $topic Meeting topic/title
     * @param string $agenda Meeting description/agenda
     * @param Carbon $startTime Start time
     * @param int $duration Duration in minutes
     * @param array $settings Additional meeting settings
     * @return array Meeting details including join URL
     */
    public function createMeeting(
        string $topic,
        string $agenda,
        Carbon $startTime,
        int $duration = 60,
        array $settings = []
    ): array {
        $token = $this->generateJWT();

        $defaultSettings = [
            'host_video' => true,
            'participant_video' => true,
            'join_before_host' => false,
            'mute_upon_entry' => true,
            'waiting_room' => true,
            'approval_type' => 0, // Automatically approve
            'audio' => 'both',
            'auto_recording' => 'none',
        ];

        $meetingData = [
            'topic' => $topic,
            'type' => 2, // Scheduled meeting
            'start_time' => $startTime->format('Y-m-d\TH:i:s'),
            'timezone' => config('app.timezone', 'UTC'),
            'duration' => $duration,
            'agenda' => $agenda,
            'settings' => array_merge($defaultSettings, $settings),
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/users/me/meetings", $meetingData);

        if (!$response->successful()) {
            throw new \Exception('Failed to create Zoom meeting: ' . $response->body());
        }

        $meetingDetails = $response->json();

        return [
            'id' => $meetingDetails['id'],
            'title' => $meetingDetails['topic'],
            'description' => $meetingDetails['agenda'],
            'start_time' => new Carbon($meetingDetails['start_time']),
            'duration' => $meetingDetails['duration'],
            'meeting_url' => $meetingDetails['join_url'],
            'password' => $meetingDetails['password'] ?? null,
            'provider' => 'zoom',
        ];
    }

    /**
     * Update an existing Zoom meeting
     *
     * @param string $meetingId
     * @param array $attributes
     * @return array Updated meeting details
     */
    public function updateMeeting(string $meetingId, array $attributes): array
    {
        $token = $this->generateJWT();

        $updateData = [];

        if (isset($attributes['title'])) {
            $updateData['topic'] = $attributes['title'];
        }

        if (isset($attributes['description'])) {
            $updateData['agenda'] = $attributes['description'];
        }

        if (isset($attributes['start_time'])) {
            $updateData['start_time'] = $attributes['start_time']->format('Y-m-d\TH:i:s');
        }

        if (isset($attributes['duration'])) {
            $updateData['duration'] = $attributes['duration'];
        }

        if (isset($attributes['settings'])) {
            $updateData['settings'] = $attributes['settings'];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->patch("{$this->baseUrl}/meetings/{$meetingId}", $updateData);

        if (!$response->successful()) {
            throw new \Exception('Failed to update Zoom meeting: ' . $response->body());
        }

        // Get updated meeting details
        $meetingResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get("{$this->baseUrl}/meetings/{$meetingId}");

        $meetingDetails = $meetingResponse->json();

        return [
            'id' => $meetingDetails['id'],
            'title' => $meetingDetails['topic'],
            'description' => $meetingDetails['agenda'],
            'start_time' => new Carbon($meetingDetails['start_time']),
            'duration' => $meetingDetails['duration'],
            'meeting_url' => $meetingDetails['join_url'],
            'password' => $meetingDetails['password'] ?? null,
            'provider' => 'zoom',
        ];
    }

    /**
     * Delete a Zoom meeting
     *
     * @param string $meetingId
     * @return boolean Success status
     */
    public function deleteMeeting(string $meetingId): bool
    {
        $token = $this->generateJWT();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->delete("{$this->baseUrl}/meetings/{$meetingId}");

        return $response->successful();
    }
}
