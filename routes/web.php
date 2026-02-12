<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OAuthClientController;
use App\Http\Controllers\DashboardImageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Start login with AISITE (your main app)
Route::get('/login/aisite', [OAuthClientController::class, 'redirectToProvider'])
    ->name('login.aisite');

// Callback URL AISITE redirects back to, with ?code=...
Route::get('/oauth/callback', [OAuthClientController::class, 'handleProviderCallback'])
    ->name('oauth.callback');

// Simple protected dashboard
Route::middleware('auth')->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Proxy endpoint for dashboard image generation
Route::middleware('auth')->post('/dashboard/image', [DashboardImageController::class, 'generate'])
    ->name('api.image.generate');
