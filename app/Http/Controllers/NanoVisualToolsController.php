<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NanoVisualToolsController extends Controller
{
    /**
     * Display the Nano Visual Tools page
     */
    public function index()
    {
        return view('nano-visual-tools.index');
    }

    /**
     * Get list of available visual tools from API.
     * Results are cached in Redis for 30 minutes to avoid redundant API calls.
     */
    public function getTools(Request $request)
    {
        $accessToken = session('aisite_access_token');

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'error' => 'Missing AISITE access token. Please log in again.',
            ], 401);
        }

        $service = config('services.aisite');

        try {
            // Cache the tools list for 30 minutes. The list is the same for all
            // authenticated users, so a shared key is appropriate. If the API
            // call fails or returns an error we throw, which prevents caching
            // the bad result.
            $payload = Cache::remember('nano_visual_tools', now()->addMinutes(30), function () use ($accessToken, $service) {
                $http = new Client(['timeout' => 30]);

                $response = $http->get(rtrim($service['internal_url'], '/') . '/api/nano-visual-tools', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Accept' => 'application/json',
                    ],
                ]);

                $data = json_decode((string) $response->getBody(), true);

                if ($response->getStatusCode() !== 200 || empty($data['success'])) {
                    throw new \RuntimeException($data['error'] ?? 'Failed to fetch tools from provider');
                }

                return $data;
            });

            return response()->json([
                'success' => true,
                'data' => $payload['data'] ?? [],
                'count' => $payload['count'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            Log::error('Nano Visual Tools API error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to contact AISITE visual tools API: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run a visual tool to generate an image
     */
    public function runTool(Request $request)
    {
        $data = $request->validate([
            'tool' => 'required|string|max:100',
            'tool_id' => 'required|integer',
            'prefix_text' => 'nullable|string|max:500',
            'features' => 'nullable|json',
            'prompt' => 'nullable|string|max:5000',
        ]);
       
        $accessToken = session('aisite_access_token');

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'error' => 'Missing AISITE access token. Please log in again.',
            ], 401);
        }

        $service = config('services.aisite');

        $http = new Client([
            'timeout' => 120, // Image generation can take time
        ]);

        try {
            // Build multipart form data for file uploads
            $multipart = [
                [
                    'name' => 'tool',
                    'contents' => $data['tool'],
                ],
                [
                    'name' => 'tool_id',
                    'contents' => (string) $data['tool_id'],
                ],
            ];

            if (!empty($data['prefix_text'])) {
                $multipart[] = [
                    'name' => 'prefix_text',
                    'contents' => $data['prefix_text'],
                ];
            }

            if (!empty($data['features'])) {
                $multipart[] = [
                    'name' => 'features',
                    'contents' => is_string($data['features']) ? $data['features'] : json_encode($data['features']),
                ];
            }

            if (!empty($data['prompt'])) {
                $multipart[] = [
                    'name' => 'prompt',
                    'contents' => $data['prompt'],
                ];
            }

            // Handle image uploads if present
            foreach ($request->allFiles() as $key => $file) {
                $multipart[] = [
                    'name' => $key,
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ];
            }

            $response = $http->post(rtrim($service['internal_url'], '/') . '/api/nano-visual-tools/run', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
                'multipart' => $multipart,
            ]);

            $payload = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() !== 200 || empty($payload['success'])) {
                return response()->json([
                    'success' => false,
                    'error' => $payload['message'] ?? $payload['error'] ?? 'Tool execution failed on provider',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'image_url' => $payload['image_url'] ?? null,
                'text_response' => $payload['text_response'] ?? null,
                'grounding_sources' => $payload['grounding_sources'] ?? [],
                'conversation_id' => $payload['conversation_id'] ?? null,
                'credits_used' => $payload['credits_used'] ?? 0,
                'image_data' => $payload['image_data'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Nano Visual Tools run error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to execute visual tool: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get images generated with a specific tool
     */
    public function getImages(Request $request)
    {
        $request->validate([
            'tool_id' => 'required|integer',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $accessToken = session('aisite_access_token');

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'error' => 'Missing AISITE access token. Please log in again.',
            ], 401);
        }

        $service = config('services.aisite');

        $http = new Client([
            'timeout' => 30,
        ]);

        try {
            $queryParams = http_build_query([
                'tool_id' => $request->input('tool_id'),
                'per_page' => $request->input('per_page', 12),
            ]);

            $response = $http->get(rtrim($service['internal_url'], '/') . '/api/nano-visual-tools/images?' . $queryParams, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
            ]);

            $payload = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() !== 200 || empty($payload['success'])) {
                return response()->json([
                    'success' => false,
                    'error' => $payload['error'] ?? 'Failed to fetch images from provider',
                ], 500);
            }

            return response()->json($payload);
        } catch (\Throwable $e) {
            Log::error('Nano Visual Tools images API error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to contact AISITE images API: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Regenerate variations of an existing image
     */
    public function regenerate(Request $request)
    {
        $data = $request->validate([
            'image_id' => 'required|integer',
            'modification_prompt' => 'nullable|string|max:1000',
            'layout_change' => 'sometimes|boolean',
            'style_change' => 'sometimes|boolean',
            'color_change' => 'sometimes|boolean',
            'lighting_change' => 'sometimes|boolean',
            'detail_enhance' => 'sometimes|boolean',
            'count' => 'nullable|integer|min:1|max:3',
        ]);

        $accessToken = session('aisite_access_token');

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'error' => 'Missing AISITE access token. Please log in again.',
            ], 401);
        }

        $service = config('services.aisite');

        $http = new Client([
            'timeout' => 180, // Regeneration can take longer
        ]);

        try {
            $response = $http->post(rtrim($service['internal_url'], '/') . '/api/nano-visual-tools/regenerate', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            $payload = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() !== 200 || empty($payload['success'])) {
                return response()->json([
                    'success' => false,
                    'error' => $payload['message'] ?? $payload['error'] ?? 'Regeneration failed on provider',
                ], 500);
            }

            return response()->json($payload);
        } catch (\Throwable $e) {
            Log::error('Nano Visual Tools regenerate error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to regenerate image: ' . $e->getMessage(),
            ], 500);
        }
    }
}
