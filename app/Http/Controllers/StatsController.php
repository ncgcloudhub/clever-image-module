<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatsController extends Controller
{
    public function index()
    {
        return view('stats.index');
    }

    /**
     * Proxy image stats from the main AISITE API.
     * Calls /api/user/stats on the main site and returns image-related data.
     */
    public function getStats(Request $request)
    {
        $accessToken = session('aisite_access_token');

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $service = config('services.aisite');
        $baseUrl = rtrim($service['internal_url'], '/');

        $client = new Client(['timeout' => 15]);

        try {
            $response = $client->get($baseUrl . '/api/user/stats', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept'        => 'application/json',
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            return response()->json($data);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $body = json_decode((string) $e->getResponse()->getBody(), true);
            Log::warning('Stats API client error', ['status' => $e->getResponse()->getStatusCode(), 'body' => $body]);
            return response()->json($body ?? ['success' => false, 'message' => 'Failed to fetch stats'], $e->getResponse()->getStatusCode());
        } catch (\Exception $e) {
            Log::error('Stats API proxy error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch stats'], 500);
        }
    }
}
