<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class RefreshAisiteToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = session('aisite_access_token');
        $issuedAt = session('aisite_token_issued_at');
        $expiresIn = session('aisite_token_expires_in', 86400);

        if (!$token || !$issuedAt) {
            return $next($request);
        }

        $elapsed = now()->timestamp - $issuedAt;
        $threshold = (int) ($expiresIn * 0.75);

        if ($elapsed < $threshold) {
            return $next($request);
        }

        try {
            $service = config('services.aisite');
            $http = new Client(['timeout' => 10]);

            $response = $http->post(rtrim($service['internal_url'], '/') . '/api/oauth/token/refresh', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);
            $newToken = $data['access_token'] ?? null;

            if ($newToken) {
                session([
                    'aisite_access_token'     => $newToken,
                    'aisite_token_issued_at'  => now()->timestamp,
                    'aisite_token_expires_in' => $data['expires_in'] ?? 86400,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('AISITE token refresh failed', [
                'error'   => $e->getMessage(),
                'elapsed' => $elapsed,
            ]);
        }

        return $next($request);
    }
}
