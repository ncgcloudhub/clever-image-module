<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CommunityGalleryController extends Controller
{
    /**
     * Display the Community Gallery page.
     */
    public function index()
    {
        return view('community-gallery');
    }

    /**
     * Proxy request to dev_ai to fetch all users' generated images (community feed).
     *
     * Query Parameters:
     * - per_page (integer, optional): Items per page (default: 20)
     * - page    (integer, optional): Page number (default: 1)
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

        $service  = config('services.aisite');
        $page     = (int) $request->input('page', 1);
        $perPage  = (int) $request->input('per_page', 20);
        $cacheKey = "community_gallery_page_{$page}_per_{$perPage}";

        try {
            // Cache each page of the community feed for 5 minutes.
            // The feed is shared across all users, so a single key per page
            // is appropriate. Errors throw to prevent caching bad results.
            $payload = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($accessToken, $service, $page, $perPage) {
                $http = new Client(['timeout' => 30]);

                $queryParams = http_build_query([
                    'per_page' => $perPage,
                    'page'     => $page,
                ]);

                $response = $http->get(
                    rtrim($service['internal_url'], '/') . '/api/gallery?' . $queryParams,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Accept'        => 'application/json',
                        ],
                    ]
                );

                $data = json_decode((string) $response->getBody(), true);

                if ($response->getStatusCode() !== 200 || empty($data['success'])) {
                    throw new \RuntimeException($data['error'] ?? 'Failed to fetch community gallery from provider');
                }

                return $data;
            });

            return response()->json($payload);
        } catch (\Throwable $e) {
            Log::error('Community Gallery API proxy error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'Failed to contact community gallery API: ' . $e->getMessage(),
            ], 500);
        }
    }
}
