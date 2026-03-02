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
            'name'     => 'sometimes|string|max:255',
            'username' => 'sometimes|nullable|string|max:255',
            'phone'    => 'sometimes|nullable|string|max:50',
            'address'  => 'sometimes|nullable|string|max:1000',
            'country'  => 'sometimes|nullable|string|max:100',
            'avatar'   => 'sometimes|nullable|string|url',
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

    public function changePassword(Request $request)
    {
        $accessToken = session('aisite_access_token');

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $data = $request->validate([
            'current_password'      => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        try {
            $service = config('services.aisite');
            $baseUrl = rtrim($service['internal_url'], '/');

            $client = new Client(['timeout' => 10]);
            $response = $client->put($baseUrl . '/api/user/password', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept'        => 'application/json',
                ],
                'json' => $data,
            ]);

            $result = json_decode((string) $response->getBody(), true);

            return response()->json($result);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $body = json_decode((string) $e->getResponse()->getBody(), true);
            return response()->json($body ?? ['success' => false, 'message' => 'Password change failed'], $e->getResponse()->getStatusCode());
        } catch (\Exception $e) {
            Log::error('Password change failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Password change failed'], 500);
        }
    }

    public function getReferrals()
    {
        $accessToken = session('aisite_access_token');

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        try {
            $service = config('services.aisite');
            $baseUrl = rtrim($service['internal_url'], '/');

            $client = new Client(['timeout' => 10]);
            $response = $client->get($baseUrl . '/api/user/referrals', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept'        => 'application/json',
                ],
            ]);

            return response($response->getBody(), $response->getStatusCode())
                ->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            Log::error('Get referrals failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to load referral codes'], 500);
        }
    }

    public function createReferral(Request $request)
    {
        $accessToken = session('aisite_access_token');

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $data = array_filter($request->only(['code']));

        try {
            $service = config('services.aisite');
            $baseUrl = rtrim($service['internal_url'], '/');

            $client = new Client(['timeout' => 10]);
            $response = $client->post($baseUrl . '/api/user/referrals', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept'        => 'application/json',
                ],
                'json' => $data,
            ]);

            $result = json_decode((string) $response->getBody(), true);

            return response()->json($result);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $body = json_decode((string) $e->getResponse()->getBody(), true);
            return response()->json($body ?? ['success' => false, 'message' => 'Failed to create referral code'], $e->getResponse()->getStatusCode());
        } catch (\Exception $e) {
            Log::error('Create referral failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to create referral code'], 500);
        }
    }

    public function deleteReferral($id)
    {
        $accessToken = session('aisite_access_token');

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        try {
            $service = config('services.aisite');
            $baseUrl = rtrim($service['internal_url'], '/');

            $client = new Client(['timeout' => 10]);
            $response = $client->delete($baseUrl . '/api/user/referrals/' . $id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept'        => 'application/json',
                ],
            ]);

            $result = json_decode((string) $response->getBody(), true);

            return response()->json($result);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $body = json_decode((string) $e->getResponse()->getBody(), true);
            return response()->json($body ?? ['success' => false, 'message' => 'Failed to delete referral code'], $e->getResponse()->getStatusCode());
        } catch (\Exception $e) {
            Log::error('Delete referral failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to delete referral code'], 500);
        }
    }
}
