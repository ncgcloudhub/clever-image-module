<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GalleryController extends Controller
{
    /**
     * Display the My Gallery page.
     */
    public function index()
    {
        return view('gallery');
    }

    /**
     * Proxy request to dev_ai to fetch the authenticated user's generated images.
     *
     * Query Parameters:
     * - per_page (integer, optional): Items per page (default: 20)
     * - page (integer, optional): Page number
     */
    public function getImages(Request $request)
    {
        $accessToken = session('aisite_access_token');

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'error'   => 'Missing AISITE access token. Please log in again.',
            ], 401);
        }

        $service = config('services.aisite');

        $http = new Client(['timeout' => 30]);

        try {
            $queryParams = http_build_query([
                'per_page' => $request->input('per_page', 20),
                'page'     => $request->input('page', 1),
            ]);

            $response = $http->get(
                rtrim($service['internal_url'], '/') . '/api/user/gallery?' . $queryParams,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Accept'        => 'application/json',
                    ],
                ]
            );

            $payload = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() !== 200 || empty($payload['success'])) {
                return response()->json([
                    'success' => false,
                    'error'   => $payload['error'] ?? 'Failed to fetch gallery from provider',
                ], 500);
            }

            return response()->json($payload);
        } catch (\Throwable $e) {
            Log::error('Gallery API proxy error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'Failed to contact gallery API: ' . $e->getMessage(),
            ], 500);
        }
    }
}
