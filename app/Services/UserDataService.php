<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class UserDataService
{
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        $service = config('services.aisite');
        $this->baseUrl = rtrim($service['base_url'], '/');
        $this->client = new Client([
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Get user details including credits, tokens, and plan from main API
     *
     * @param string $accessToken
     * @return array|null
     */
    public function getUserDetails(string $accessToken): ?array
    {
        try {
            $url = $this->baseUrl . '/api/user/details';

            Log::info('Fetching user details', [
                'url' => $url,
                'has_token' => !empty($accessToken),
            ]);

            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            Log::info('User details response', [
                'status' => $response->getStatusCode(),
                'success' => $data['success'] ?? false,
                'has_data' => isset($data['data']),
            ]);

            if ($data['success'] ?? false) {
                return $data['data'];
            }

            Log::warning('User details fetch failed - success is false', [
                'response' => $data,
            ]);

            return null;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = null;
            if ($e->hasResponse()) {
                $responseBody = (string) $e->getResponse()->getBody();
            }

            Log::error('Failed to fetch user details from main API - Request Exception', [
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
                'response_body' => $responseBody,
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to fetch user details from main API - General Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Get user credits only
     *
     * @param string $accessToken
     * @return array|null
     */
    public function getUserCredits(string $accessToken): ?array
    {
        try {
            $response = $this->client->get($this->baseUrl . '/api/user/credits', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            if ($data['success'] ?? false) {
                return $data['data'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to fetch user credits from main API', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
