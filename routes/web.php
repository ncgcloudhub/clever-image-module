<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\OAuthClientController;
use App\Http\Controllers\DashboardImageController;
use App\Http\Controllers\NanoVisualToolsController;
use App\Http\Controllers\NanoBananaPlaygroundController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\CommunityGalleryController;

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

// Login route - redirects to OAuth login
Route::get('/login', function () {
    return redirect()->route('login.aisite');
})->name('login');

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

// Logout route
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Proxy endpoint for dashboard image generation
Route::middleware('auth')->post('/dashboard/image', [DashboardImageController::class, 'generate'])
    ->name('api.image.generate');

// ==================================
// NANO VISUAL TOOLS ROUTES
// ==================================
// These routes provide a client interface for the Nano Visual Tools API
Route::middleware('auth')->group(function () {
    // Main visual tools page
    Route::get('/nano-visual-tools', [NanoVisualToolsController::class, 'index'])
        ->name('nano.visual.tools');
    
    // API proxy endpoints (these call the main AISITE API)
    Route::get('/api/nano-visual-tools', [NanoVisualToolsController::class, 'getTools'])
        ->name('api.nano.visual.tools.get');
    
    Route::post('/api/nano-visual-tools/run', [NanoVisualToolsController::class, 'runTool'])
        ->name('api.nano.visual.tools.run');
    
    Route::get('/api/nano-visual-tools/images', [NanoVisualToolsController::class, 'getImages'])
        ->name('api.nano.visual.tools.images');
    
    Route::post('/api/nano-visual-tools/regenerate', [NanoVisualToolsController::class, 'regenerate'])
        ->name('api.nano.visual.tools.regenerate');

    // ==================================
    // NANO BANANA PLAYGROUND ROUTES
    // ==================================
    // Canvas playground (blank canvas + minichat)
    Route::get('/playground/canvas', [NanoBananaPlaygroundController::class, 'canvas'])
        ->name('playground.canvas');

    // Shared API proxy routes (used by both playground views)
    Route::post('/playground/api/chat', [NanoBananaPlaygroundController::class, 'chat'])
        ->name('playground.api.chat');

    Route::get('/playground/api/sessions', [NanoBananaPlaygroundController::class, 'getSessions'])
        ->name('playground.api.sessions');

    Route::post('/playground/api/session', [NanoBananaPlaygroundController::class, 'saveSession'])
        ->name('playground.api.session.save');

    Route::get('/playground/api/session/{sessionId}', [NanoBananaPlaygroundController::class, 'getSession'])
        ->name('playground.api.session.get');

    Route::delete('/playground/api/session/{sessionId}', [NanoBananaPlaygroundController::class, 'deleteSession'])
        ->name('playground.api.session.delete');

    // Gallery routes
    Route::get('/gallery', [GalleryController::class, 'index'])
        ->name('gallery');
    Route::get('/api/gallery', [GalleryController::class, 'getImages'])
        ->name('api.gallery');

    // Community Gallery routes
    Route::get('/community-gallery', [CommunityGalleryController::class, 'index'])
        ->name('community.gallery');
    Route::get('/api/community-gallery', [CommunityGalleryController::class, 'getImages'])
        ->name('api.community.gallery');
});
