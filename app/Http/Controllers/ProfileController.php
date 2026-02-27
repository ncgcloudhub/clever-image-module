<?php

namespace App\Http\Controllers;

use App\Services\UserDataService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function index()
    {
        $accessToken = session('aisite_access_token');
        $userData = null;

        if ($accessToken) {
            $service = new UserDataService();
            $userData = $service->getUserDetails($accessToken);
        }

        return view('profile.index', compact('userData'));
    }

    public function update(Request $request)
    {
        $accessToken = session('aisite_access_token');

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $data = $request->validate([
            'name'   => 'sometimes|string|max:255',
            'avatar' => 'sometimes|nullable|string|url',
        ]);

        try {
            $service = config('services.aisite');
            $baseUrl = rtrim($service['internal_url'], '/');

            $client = new Client(['timeout' => 10]);
            $response = $client->put($baseUrl . '/api/user/profile', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept'        => 'application/json',
                ],
                'json' => $data,
            ]);

            $result = json_decode((string) $response->getBody(), true);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Profile update failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Profile update failed'], 500);
        }
    }
}
