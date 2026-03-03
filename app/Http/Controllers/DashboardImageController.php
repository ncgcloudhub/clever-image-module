<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardImageController extends Controller
{
    private function providerError(\Throwable $e, string $fallback)
    {
        if ($e instanceof \GuzzleHttp\Exception\ClientException && $e->hasResponse()) {
            $status = $e->getResponse()->getStatusCode();
            $body   = (string) $e->getResponse()->getBody();
            $json   = json_decode($body, true);

            if (is_array($json)) {
                return response()->json($json, $status);
            }
        }

        return response()->json([
            'success' => false,
            'error'   => $fallback,
        ], 500);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'prompt' => 'required|string|max:4000',
        ]);

        $accessToken = session('aisite_access_token');

        if (! $accessToken) {
            return response()->json([
                'success' => false,
                'error'   => 'Missing AISITE access token. Please log in again.',
            ], 401);
        }

        $service = config('services.aisite');

        $http = new Client([
            'timeout' => 60,
        ]);

        try {
            $response = $http->post(rtrim($service['internal_url'], '/') . '/api/oauth/nano-image', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept'        => 'application/json',
                ],
                'json' => [
                    'prompt' => $data['prompt'],
                ],
            ]);

            $payload = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() !== 200 || empty($payload['success'])) {
                return response()->json([
                    'success' => false,
                    'error'   => $payload['error'] ?? 'Image generation failed on provider',
                ], 500);
            }

            return response()->json([
                'success'   => true,
                'image_url' => $payload['image_url'] ?? null,
                'prompt'    => $payload['prompt'] ?? $data['prompt'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Dashboard image generation error', [
                'message' => $e->getMessage(),
            ]);
            return $this->providerError($e, 'Image generation is temporarily unavailable. Please try again.');
        }
    }
}




