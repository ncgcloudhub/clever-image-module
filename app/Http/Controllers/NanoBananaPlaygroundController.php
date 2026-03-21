<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NanoBananaPlaygroundController extends Controller
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

    /**
     * Canvas-style playground page.
     */
    public function canvas()
    {
        $pool     = $this->loadCanvasExamples(); // cached pool of up to 18
        shuffle($pool);
        $examples = array_slice($pool, 0, 3);
        return view('playground.canvas', compact('examples'));
    }

    /**
     * Fetch up to 18 community gallery images to use as canvas examples.
     * Cached for 15 minutes to avoid an API call on every page load.
     * The caller shuffles the pool so different 3 are shown each refresh.
     */
    private function loadCanvasExamples(): array
    {
        $token = session('aisite_access_token');
        if (!$token) return [];

        return Cache::remember('canvas_studio_examples', now()->addHours(2), function () use ($token) {
            try {
                $response = (new Client(['timeout' => 10]))->get(
                    rtrim(config('services.aisite.internal_url'), '/') . '/api/studio-examples',
                    ['headers' => ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json']]
                );

                $data = json_decode($response->getBody()->getContents(), true);
                return array_values(array_filter(
                    $data['data'] ?? [],
                    fn($i) => !empty($i['image_url']) && !empty($i['prompt'])
                ));

            } catch (\Throwable $e) {
                Log::warning('Canvas studio examples fetch failed', ['message' => $e->getMessage()]);
                return [];
            }
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function http(): Client
    {
        return new Client(['timeout' => 180]);
    }

    private function accessToken(): ?string
    {
        return session('aisite_access_token');
    }

    private function baseUrl(): string
    {
        return rtrim(config('services.aisite.base_url'), '/');
    }

    private function unauthorised(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'error'   => 'Missing AISITE access token. Please log in again.',
        ], 401);
    }

    // ─── API Proxy Methods ────────────────────────────────────────────────────

    /**
     * POST /playground/api/chat
     */
    public function chat(Request $request)
    {
        set_time_limit(300);

        $request->validate([
            'prompt'          => 'required|string|max:5000',
            'canvas_image'    => 'nullable|image|mimes:png,jpg,jpeg,webp|max:10240',
            'conversation_id' => 'nullable|string',
        ]);

        $token = $this->accessToken();
        if (!$token) return $this->unauthorised();

        try {
            $multipart = [
                ['name' => 'prompt', 'contents' => $request->input('prompt')],
            ];

            if ($request->input('conversation_id')) {
                $multipart[] = ['name' => 'conversation_id', 'contents' => $request->input('conversation_id')];
            }

            if ($request->hasFile('canvas_image')) {
                $file        = $request->file('canvas_image');
                $multipart[] = [
                    'name'     => 'canvas_image',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ];
            }

            $response = $this->http()->post($this->baseUrl() . '/api/nano-banana/chat', [
                'headers'   => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                ],
                'multipart' => $multipart,
            ]);

            return response($response->getBody()->getContents(), $response->getStatusCode())
                ->header('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            Log::error('Playground chat proxy error', ['message' => $e->getMessage()]);
            return $this->providerError($e, 'Chat request failed. Please try again.');
        }
    }

    /**
     * GET /playground/api/sessions
     */
    public function getSessions(Request $request)
    {
        $token = $this->accessToken();
        if (!$token) return $this->unauthorised();

        try {
            $response = $this->http()->get($this->baseUrl() . '/api/nano-banana/sessions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                ],
            ]);

            return response($response->getBody()->getContents(), $response->getStatusCode())
                ->header('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            Log::error('Playground getSessions proxy error', ['message' => $e->getMessage()]);
            return $this->providerError($e, 'Unable to load sessions right now.');
        }
    }

    /**
     * POST /playground/api/session
     */
    public function saveSession(Request $request)
    {
        $token = $this->accessToken();
        if (!$token) return $this->unauthorised();

        try {
            $response = $this->http()->post($this->baseUrl() . '/api/nano-banana/session', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ],
                'json' => $request->all(),
            ]);

            return response($response->getBody()->getContents(), $response->getStatusCode())
                ->header('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            Log::error('Playground saveSession proxy error', ['message' => $e->getMessage()]);
            return $this->providerError($e, 'Unable to save session right now.');
        }
    }

    /**
     * GET /playground/api/session/{sessionId}
     */
    public function getSession(Request $request, string $sessionId)
    {
        $token = $this->accessToken();
        if (!$token) return $this->unauthorised();

        try {
            $response = $this->http()->get($this->baseUrl() . '/api/nano-banana/session/' . $sessionId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                ],
            ]);

            return response($response->getBody()->getContents(), $response->getStatusCode())
                ->header('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            Log::error('Playground getSession proxy error', ['message' => $e->getMessage()]);
            return $this->providerError($e, 'Unable to load this session right now.');
        }
    }

    /**
     * DELETE /playground/api/session/{sessionId}
     */
    public function deleteSession(Request $request, string $sessionId)
    {
        $token = $this->accessToken();
        if (!$token) return $this->unauthorised();

        try {
            $response = $this->http()->delete($this->baseUrl() . '/api/nano-banana/session/' . $sessionId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                ],
            ]);

            return response($response->getBody()->getContents(), $response->getStatusCode())
                ->header('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            Log::error('Playground deleteSession proxy error', ['message' => $e->getMessage()]);
            return $this->providerError($e, 'Unable to delete this session right now.');
        }
    }
}
