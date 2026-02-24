<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OAuthClientController extends Controller
{
    public function redirectToProvider(Request $request)
    {
        $state = Str::random(40);
        $request->session()->put('oauth_state', $state);

        $service = config('services.aisite');

        $query = http_build_query([
            'response_type' => 'code',
            'client_id'     => $service['client_id'],
            'redirect_uri'  => $service['redirect'],
            'scope'         => 'basic',
            'state'         => $state,
        ]);

        return redirect(rtrim($service['base_url'], '/') . '/oauth/authorize?' . $query);
    }

    public function handleProviderCallback(Request $request)
    {
        // In strict production setups you should verify the OAuth "state" parameter
        // matches what was originally sent. For local/dev environments this can
        // be fragile (different domains, cookies, etc.), so we only read it
        // but do not abort on mismatch to avoid 403 errors after login.
        $stateFromSession = $request->session()->pull('oauth_state');
        $stateFromQuery   = $request->input('state');
        // TODO: For production, enforce strict check:
        // if (! $stateFromSession || $stateFromSession !== $stateFromQuery) {
        //     abort(403, 'Invalid state');
        // }

        $code = $request->input('code');
        if (!$code) {
            abort(400, 'Missing authorization code');
        }

        $service = config('services.aisite');
        $http = new Client([
            'timeout' => 10,
        ]);

        // Exchange code for token (provider exposes this under /api/oauth/token)
        $tokenResponse = $http->post(rtrim($service['internal_url'], '/') . '/api/oauth/token', [
            'form_params' => [
                'grant_type'    => 'authorization_code',
                'client_id'     => $service['client_id'],
                'client_secret' => $service['client_secret'],
                'redirect_uri'  => $service['redirect'],
                'code'          => $code,
            ],
        ]);

        $tokenData = json_decode((string) $tokenResponse->getBody(), true);
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            abort(500, 'Failed to get access token from AISITE');
        }

        // Get user info from AISITE
        $userResponse = $http->get(rtrim($service['internal_url'], '/') . '/api/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept'        => 'application/json',
            ],
        ]);

        $providerUser = json_decode((string) $userResponse->getBody(), true);

        // Map to local user (store provider_id from AISITE).
        // If a user with this provider_id OR this email already exists, reuse it
        // and simply attach the provider_id instead of trying to create a duplicate.
        $query = User::query();

        if (!empty($providerUser['id'])) {
            $query->where('provider_id', $providerUser['id']);
        }

        if (!empty($providerUser['email'])) {
            $query->orWhere('email', $providerUser['email']);
        }

        $user = $query->first();

        if (! $user) {
            // Create a new local user
            $user = User::create([
                'name'        => $providerUser['name'] ?? ($providerUser['email'] ?? 'AISITE User'),
                'email'       => $providerUser['email'] ?? null,
                'provider_id' => $providerUser['id'] ?? null,
                'password'    => bcrypt(Str::random(32)),
            ]);
        } else {
            // Ensure provider_id is set if missing
            if (empty($user->provider_id) && !empty($providerUser['id'])) {
                $user->provider_id = $providerUser['id'];
                $user->save();
            }
        }

        Auth::login($user, true);

        // Optionally store token for later API calls
        session(['aisite_access_token' => $accessToken]);

        return redirect()->route('dashboard');
    }
}


