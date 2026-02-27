<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImageGeneratorController extends Controller
{
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
        return response()->json(['success' => false, 'error' => 'Missing access token. Please log in again.'], 401);
    }

    /**
     * GET /image-generator
     * Fetch providers + models and render the page.
     */
    public function index()
    {
        $providers = [];
        $token     = $this->accessToken();

        if ($token) {
            try {
                $response  = $this->http()->get($this->baseUrl() . '/api/image-generator/providers', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept'        => 'application/json',
                    ],
                ]);
                $payload   = json_decode($response->getBody()->getContents(), true);
                $providers = $payload['data'] ?? [];
            } catch (\Throwable $e) {
                Log::error('ImageGenerator providers fetch error', ['message' => $e->getMessage()]);
            }
        }

        return view('image-generator.index', compact('providers'));
    }

    /**
     * POST /image-generator/generate
     * Proxy the generate request to dev_ai, supporting multipart (reference images).
     */
    public function generate(Request $request)
    {
        $token = $this->accessToken();
        if (!$token) return $this->unauthorised();

        try {
            $fields = $request->only([
                'model_id', 'prompt', 'negative_prompt', 'resolution',
                'style', 'quality', 'output_format', 'num_images',
                'guidance_scale', 'steps', 'seed', 'additional_parameters',
            ]);

            if ($request->hasFile('reference_images')) {
                $multipart = [];
                foreach ($fields as $key => $value) {
                    $multipart[] = [
                        'name'     => $key,
                        'contents' => is_array($value) ? json_encode($value) : (string) $value,
                    ];
                }
                foreach ($request->file('reference_images') as $file) {
                    $multipart[] = [
                        'name'     => 'reference_images[]',
                        'contents' => fopen($file->getRealPath(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                    ];
                }
                $response = $this->http()->post($this->baseUrl() . '/api/image-generator/generate', [
                    'headers'   => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept'        => 'application/json',
                    ],
                    'multipart' => $multipart,
                ]);
            } else {
                $response = $this->http()->post($this->baseUrl() . '/api/image-generator/generate', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept'        => 'application/json',
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => $fields,
                ]);
            }

            return response($response->getBody()->getContents(), $response->getStatusCode())
                ->header('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            Log::error('ImageGenerator generate error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /image-generator/provider/{providerId}/models
     */
    public function getProviderModels(int $providerId)
    {
        $token = $this->accessToken();
        if (!$token) return $this->unauthorised();

        try {
            $response = $this->http()->get(
                $this->baseUrl() . '/api/image-generator/provider/' . $providerId . '/models',
                ['headers' => ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json']]
            );
            return response($response->getBody()->getContents(), $response->getStatusCode())
                ->header('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            Log::error('ImageGenerator provider models error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /image-generator/autocomplete
     */
    public function getAutocomplete(Request $request)
    {
        $token = $this->accessToken();
        if (!$token) return $this->unauthorised();

        try {
            $response = $this->http()->get($this->baseUrl() . '/api/image-generator/autocomplete', [
                'headers' => ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json'],
                'query'   => $request->only(['query']),
            ]);
            return response($response->getBody()->getContents(), $response->getStatusCode())
                ->header('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /image-generator/prompt-history
     */
    public function getPromptHistory(Request $request)
    {
        $token = $this->accessToken();
        if (!$token) return $this->unauthorised();

        try {
            $response = $this->http()->get($this->baseUrl() . '/api/image-generator/prompt-history', [
                'headers' => ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json'],
                'query'   => $request->only(['sort', 'limit']),
            ]);
            return response($response->getBody()->getContents(), $response->getStatusCode())
                ->header('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
