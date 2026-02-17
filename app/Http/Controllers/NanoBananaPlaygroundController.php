<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NanoBananaPlaygroundController extends Controller
{
    /**
     * Display the playground page.
     */
    public function index()
    {
        return view('playground.index');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function http(): Client
    {
        return new Client(['timeout' => 130]);
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

    // ─── API Proxy Methods ───────────────────────────────────────────────────

    /**
     * POST /playground/api/chat
     * Proxy multipart request (prompt + optional image) to AISITE.
     */
    public function chat(Request $request)
    {
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
                'headers'    => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                ],
                'multipart' => $multipart,
            ]);

            return response($response->getBody()->getContents(), $response->getStatusCode())
                ->header('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            Log::error('Playground chat proxy error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
