<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected string $expoApiUrl = 'https://exp.host/--/api/v2/push/send';

    /**
     * Send Expo push notification to a specific token.
     *
     * @param string $pushToken
     * @param string $title
     * @param string $body
     * @param mixed $data
     * @return array
     */
    public function sendPushNotification(string $pushToken, string $title, string $body, $data = null): array
    {
        $payload = [
            'to' => $pushToken,
            'title' => $title,
            'body' => $body,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->expoApiUrl, $payload);

            return [
                'status' => 'success',
                'response' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error("PushNotificationService Error: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
