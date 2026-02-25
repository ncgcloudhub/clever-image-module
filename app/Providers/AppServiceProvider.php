<?php

namespace App\Providers;

use App\Services\UserDataService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        // Share user data with sidebar and topbar
        View::composer(['layouts.sidebar', 'layouts.topbar'], function ($view) {
            $userData = null;
            $accessToken = session('aisite_access_token');

            if ($accessToken) {
                $userDataService = new UserDataService();
                $userData = $userDataService->getUserDetails($accessToken);

                // Log for debugging
                Log::info('User data fetched for view', [
                    'has_token' => !empty($accessToken),
                    'has_data' => !is_null($userData),
                    'data' => $userData,
                ]);
            }

            $view->with('userData', $userData);
        });
    }
}
