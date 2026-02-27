<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class BillingPageController extends Controller
{
    public function index()
    {
        $accessToken = session('aisite_access_token');
        $billingData = null;

        if ($accessToken) {
            try {
                $service = config('services.aisite');
                $baseUrl = rtrim($service['internal_url'], '/');

                $client = new Client(['timeout' => 10]);
                $response = $client->get($baseUrl . '/api/user/billing', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Accept'        => 'application/json',
                    ],
                ]);

                $result = json_decode((string) $response->getBody(), true);

                if ($result['success'] ?? false) {
                    $billingData = $result['data'];
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch billing data', ['error' => $e->getMessage()]);
            }
        }

        return view('billing.index', compact('billingData'));
    }
}
