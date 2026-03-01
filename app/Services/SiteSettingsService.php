<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SiteSettingsService
{
    protected Client $client;
    protected string $baseUrl;

    public function __construct()
    {
        $service = config('services.aisite');
        $this->baseUrl = rtrim($service['internal_url'] ?? $service['base_url'], '/');
        $this->client = new Client([
            'timeout' => 8,
            'headers' => ['Accept' => 'application/json'],
        ]);
    }

    /**
     * Fetch site settings from the main AISITE API.
     * Results are cached for 30 minutes to avoid repeated API calls.
     *
     * @return array|null
     */
    public function getSettings(): ?array
    {
        return Cache::remember('site_settings', 1800, function () {
            try {
                $response = $this->client->get($this->baseUrl . '/api/site-settings');
                $data = json_decode((string) $response->getBody(), true);

                if ($data['success'] ?? false) {
                    return $data['data'];
                }

                Log::warning('SiteSettings fetch returned success=false', ['response' => $data]);
                return null;
            } catch (\Exception $e) {
                Log::error('Failed to fetch site settings', ['error' => $e->getMessage()]);
                return null;
            }
        });
    }
}
