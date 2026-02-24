# OAuth 2.0 Authentication — Complete Setup Documentation

This document covers **everything** required to wire up OAuth 2.0 Authorization Code authentication between:

- **AISITE** (the OAuth *server* / identity provider) — `C:\AISITENEW`, runs on `http://127.0.0.1:8000`
- **oauth-client** (the OAuth *client* / third-party app) — `D:\oauth-client`, runs on `http://localhost:8001`

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [AISITE — The OAuth Server](#2-aisite--the-oauth-server)
   - 2.1 [What already exists](#21-what-already-exists)
   - 2.2 [Database tables](#22-database-tables)
   - 2.3 [Key models](#23-key-models)
   - 2.4 [OAuth endpoints](#24-oauth-endpoints)
   - 2.5 [How to register a client application](#25-how-to-register-a-client-application)
   - 2.6 [How to get the credentials](#26-how-to-get-the-credentials)
3. [oauth-client — The Client App](#3-oauth-client--the-client-app)
   - 3.1 [Create the Laravel project from scratch](#31-create-the-laravel-project-from-scratch)
   - 3.2 [Install dependencies](#32-install-dependencies)
   - 3.3 [Database migration — users table](#33-database-migration--users-table)
   - 3.4 [.env configuration](#34-env-configuration)
   - 3.5 [config/services.php](#35-configservicesphp)
   - 3.6 [User model](#36-user-model)
   - 3.7 [OAuthClientController](#37-oauthclientcontroller)
   - 3.8 [Routes](#38-routes)
4. [Full Authentication Flow — Step by Step](#4-full-authentication-flow--step-by-step)
5. [Quick-Start Checklist](#5-quick-start-checklist)

---

## 1. Architecture Overview

```
┌──────────────────────────────────────┐       ┌──────────────────────────────────────┐
│         AISITE (OAuth Server)        │       │       oauth-client (Client App)      │
│       http://127.0.0.1:8000          │       │       http://localhost:8001           │
│                                      │       │                                      │
│  • Stores oauth_applications table   │       │  • No users table with passwords     │
│  • Validates client_id / secret      │       │  • Redirects browser to AISITE login │
│  • Shows login form (existing auth)  │       │  • Receives ?code= callback          │
│  • Issues authorization codes        │       │  • Exchanges code for access token   │
│  • Issues Sanctum access tokens      │◄──────►  • Calls /api/user to get user info  │
│  • Exposes /api/user endpoint        │       │  • Creates/finds local user row      │
│                                      │       │  • Logs user in with Auth::login()   │
└──────────────────────────────────────┘       └──────────────────────────────────────┘
```

**Flow summary:**
1. User clicks "Login" on the client app.
2. Client redirects browser to `AISITE/oauth/authorize?client_id=...&redirect_uri=...&state=...`.
3. AISITE checks if the user is already logged in (its own session). If not, shows its login page.
4. After login, AISITE generates a one-time **authorization code** and redirects back to the client's callback URL with `?code=...`.
5. Client POSTs `code` + credentials to `AISITE/api/oauth/token` and receives a **Bearer access token**.
6. Client calls `AISITE/api/user` with the token to fetch the user's profile.
7. Client creates (or finds) a local `users` row mapped by `provider_id`, then calls `Auth::login()`.

---

## 2. AISITE — The OAuth Server

### 2.1 What already exists

AISITE is a full Laravel 10 application with:
- Standard Breeze/custom authentication (email + password).
- **Laravel Sanctum** (`laravel/sanctum ^3.3`) — used to issue access tokens.
- A custom OAuth 2.0 Authorization Code implementation (no Laravel Passport).

### 2.2 Database tables

Two tables power the OAuth server:

**`oauth_applications`** — stores registered client apps.

```php
// database/migrations/2026_02_12_000000_create_oauth_applications_table.php
Schema::create('oauth_applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // owner
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('client_id')->unique();       // e.g. "client_r1tkShsLmyxPx8bh7xZs3PcKhjGcSTEd"
    $table->string('client_secret')->unique();   // 64-char random string
    $table->text('redirect_uris');               // newline-separated list
    $table->string('website_url')->nullable();
    $table->string('logo_url')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_used_at')->nullable();
    $table->timestamp('created_at')->nullable();
    $table->timestamp('updated_at')->nullable();
    $table->index('user_id');
    $table->index('client_id');
});
```

**`oauth_authorization_codes`** — stores short-lived codes issued during the flow.

```php
// database/migrations/2026_02_12_000001_create_oauth_authorization_codes_table.php
Schema::create('oauth_authorization_codes', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();                       // 64-char random string
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('oauth_application_id')
          ->constrained('oauth_applications')->onDelete('cascade');
    $table->text('redirect_uri');
    $table->timestamp('expires_at');    // code expires in 10 minutes
    $table->timestamp('used_at')->nullable();
    $table->timestamps();
});
```

Run migrations in AISITE:

```bash
php artisan migrate
```

### 2.3 Key models

**`App\Models\OAuthApplication`** — `app/Models/OAuthApplication.php`

Important methods:

```php
// Generate a new client_id and client_secret pair
public static function generateCredentials(): array
{
    return [
        'client_id'     => 'client_' . Str::random(32),
        'client_secret' => Str::random(64),
    ];
}

// Validate that client_id + client_secret are correct and app is active
public static function validateCredentials($clientId, $clientSecret): ?OAuthApplication

// Check whether a redirect_uri is in the app's allowed list
public function isRedirectUriAuthorized($uri): bool

// Returns redirect_uris as an array (stored as newline-separated string)
public function getRedirectUrisAsArray(): array
```

**`App\Models\OAuthAuthorizationCode`** — `app/Models/OAuthAuthorizationCode.php`

```php
public function isExpired(): bool   // true if expires_at is in the past
public function isUsed(): bool      // true if used_at is not null
```

### 2.4 OAuth endpoints

All defined in AISITE's route files.

#### `GET /oauth/authorize` — Authorization endpoint (`routes/web.php`)

Protected by `middleware(['auth'])` — AISITE's own session auth. If the user is not logged in, they are redirected to AISITE's login page first.

**Expected query parameters:**

| Parameter       | Required | Description                                      |
|----------------|----------|--------------------------------------------------|
| `response_type` | yes      | Must be `code`                                   |
| `client_id`    | yes      | The `client_id` from `oauth_applications`        |
| `redirect_uri` | yes      | Must exactly match one of the app's `redirect_uris` |
| `state`        | no       | Random string for CSRF protection                |

**What it does:**
1. Validates that `client_id` exists and `is_active = true`.
2. Validates that `redirect_uri` is in the app's allowed list.
3. Creates a record in `oauth_authorization_codes` with a 10-minute expiry.
4. Redirects to `redirect_uri?code={64-char-code}&state={state}`.

> Auto-approves without a consent screen (by design — this is an internal first-party OAuth server).

#### `POST /api/oauth/token` — Token endpoint (`routes/api.php`)

No auth middleware — called server-to-server by the client app.

**Request body (form data or JSON):**

| Parameter       | Required | Description                                  |
|----------------|----------|----------------------------------------------|
| `grant_type`   | yes      | Must be `authorization_code`                 |
| `client_id`    | yes      | OAuth app's `client_id`                      |
| `client_secret`| yes      | OAuth app's `client_secret`                  |
| `code`         | yes      | The code received from `/oauth/authorize`    |
| `redirect_uri` | yes      | Must exactly match what was used at step 1   |

**What it does:**
1. Validates `client_id` + `client_secret` via `OAuthApplication::validateCredentials()`.
2. Looks up the authorization code in `oauth_authorization_codes`.
3. Verifies `redirect_uri` matches, code is not expired, and code is not already used.
4. Marks the code as used (`used_at = now()`).
5. Issues a Sanctum personal access token: `$user->createToken('oauth-app-{appId}')->plainTextToken`.
6. Returns JSON:

```json
{
  "access_token": "1|xxxxxxxxxxxxxxxxxxxxxxxx",
  "token_type":   "Bearer",
  "expires_in":   3600,
  "user_id":      42,
  "app_name":     "My Client App"
}
```

#### `GET /api/user` — User profile endpoint (`routes/api.php`)

Protected by `middleware('auth:sanctum')`.

```php
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
```

Returns the authenticated user's data as JSON (standard Laravel User model fields: `id`, `name`, `email`, etc.).

### 2.5 How to register a client application

You must be **logged in to AISITE** as an admin or any regular user.

1. Go to: `http://127.0.0.1:8000/oauth/applications`
2. Click **"Create New Application"** → goes to `/oauth/applications/create`
3. Fill in the form:

| Field           | Example value                                  | Notes                                              |
|----------------|------------------------------------------------|----------------------------------------------------|
| **Name**       | `oauth-client Dev App`                         | Human-readable label                               |
| **Description**| `Local dev client for testing OAuth login`    | Optional                                           |
| **Redirect URIs** | `http://localhost:8001/oauth/callback`      | One URI per line. Must match exactly what the client sends. |
| **Website URL**| `http://localhost:8001`                        | Optional                                           |
| **Logo URL**   | *(blank)*                                      | Optional                                           |

4. Click **"Create Application"**.

AISITE automatically generates `client_id` and `client_secret` via:

```php
// OAuthApplication::generateCredentials()
'client_id'     => 'client_' . Str::random(32),  // e.g. client_r1tkShsLmyxPx8bh7xZs3PcKhjGcSTEd
'client_secret' => Str::random(64),               // e.g. AVqnJbXjuTrlA4jZesx3jUXyXrCiNFHRWDrzFOYTyvfIwQlUCXJdtsX0bF7XP1Sd
```

### 2.6 How to get the credentials

After creation, AISITE redirects to the **application detail page** (`/oauth/applications/{id}`).

The credentials are shown **once** immediately after creation (via `session('show_credentials', true)`).

Copy both values immediately:
- **Client ID** — starts with `client_`, 39 characters total
- **Client Secret** — 64 random characters

> If you miss them, use the **"Regenerate Credentials"** button on the show page. This invalidates the old credentials and creates new ones.

You can also manage the application from that page:
- Edit name / redirect URIs
- Toggle active/inactive
- Assign API permissions

---

## 3. oauth-client — The Client App

### 3.1 Create the Laravel project from scratch

```bash
# Create new Laravel 10 project
composer create-project laravel/laravel oauth-client "^10.0"

cd oauth-client

# The project runs on a different port than AISITE (which uses 8000)
php artisan serve --port=8001
```

### 3.2 Install dependencies

The client needs **Sanctum** (for its own local auth) and **Guzzle** (for HTTP calls to AISITE):

```bash
composer require laravel/sanctum:^3.3 guzzlehttp/guzzle:^7.2
```

Publish and run Sanctum migrations:

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 3.3 Database migration — users table

The client stores a minimal local user record. The key addition is `provider_id`, which maps to the user's `id` on AISITE.

Modify (or replace) the default `2014_10_12_000000_create_users_table.php`:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique()->nullable();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    // Stores the user's ID from the AISITE OAuth provider
    $table->string('provider_id')->unique()->nullable();
    $table->rememberToken();
    $table->timestamps();
});
```

The `provider_id` column is critical — it is used to look up an existing local user on subsequent logins so a duplicate is never created.

Run the migration:

```bash
php artisan migrate
```

### 3.4 .env configuration

```dotenv
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:TD1tnx0B3p6FRaS/AR2aHQMeXWqoTizy7NS5u+/yWw0=
APP_DEBUG=true
APP_URL=http://localhost

# --- Database (PostgreSQL in this setup) ---
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=oauth
DB_USERNAME=postgres
DB_PASSWORD=your_password

# --- Session / Cache ---
SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_DRIVER=file

# -------------------------------------------------------
# AISITE OAuth Server Credentials
# These come from the application you registered in AISITE
# -------------------------------------------------------
AISITE_OAUTH_BASE_URL=http://127.0.0.1:8000/
AISITE_OAUTH_CLIENT_ID=client_r1tkShsLmyxPx8bh7xZs3PcKhjGcSTEd
AISITE_OAUTH_CLIENT_SECRET=AVqnJbXjuTrlA4jZesx3jUXyXrCiNFHRWDrzFOYTyvfIwQlUCXJdtsX0bF7XP1Sd
AISITE_OAUTH_REDIRECT_URI=http://localhost:8001/oauth/callback
```

**Variable explanations:**

| Variable                     | Where it comes from                                                         |
|-----------------------------|----------------------------------------------------------------------------|
| `AISITE_OAUTH_BASE_URL`     | The URL where AISITE is running (no trailing slash needed — code handles it) |
| `AISITE_OAUTH_CLIENT_ID`    | Copied from AISITE after creating the OAuth application                    |
| `AISITE_OAUTH_CLIENT_SECRET`| Copied from AISITE after creating the OAuth application                    |
| `AISITE_OAUTH_REDIRECT_URI` | The callback URL on **this** client app — must exactly match what you entered in AISITE |

### 3.5 config/services.php

Add the `aisite` service block so the `.env` values are accessible via `config('services.aisite')`:

```php
// config/services.php

'aisite' => [
    'base_url'      => env('AISITE_OAUTH_BASE_URL'),
    'client_id'     => env('AISITE_OAUTH_CLIENT_ID'),
    'client_secret' => env('AISITE_OAUTH_CLIENT_SECRET'),
    'redirect'      => env('AISITE_OAUTH_REDIRECT_URI'),
],
```

### 3.6 User model

The User model must have `HasApiTokens` (from Sanctum), and `provider_id` must be in `$fillable`.

`app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'provider_id',   // <-- required: maps to AISITE user ID
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];
}
```

### 3.7 OAuthClientController

Create `app/Http/Controllers/OAuthClientController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OAuthClientController extends Controller
{
    /**
     * Step 1 — Redirect the browser to AISITE's authorization endpoint.
     *
     * URL built: GET http://127.0.0.1:8000/oauth/authorize
     *              ?response_type=code
     *              &client_id={AISITE_OAUTH_CLIENT_ID}
     *              &redirect_uri={AISITE_OAUTH_REDIRECT_URI}
     *              &scope=basic
     *              &state={random40chars}
     */
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

    /**
     * Step 2 — AISITE redirects back here with ?code=... (and optionally ?state=...).
     *
     * This method:
     *   a) Reads the authorization code from the query string.
     *   b) POSTs to AISITE /api/oauth/token to exchange the code for an access token.
     *   c) Calls AISITE /api/user with the access token to fetch user info.
     *   d) Finds or creates a local User record mapped by provider_id.
     *   e) Logs the user in and redirects to the dashboard.
     */
    public function handleProviderCallback(Request $request)
    {
        // State check — in production, uncomment and enforce this:
        // $stateFromSession = $request->session()->pull('oauth_state');
        // if (! $stateFromSession || $stateFromSession !== $request->input('state')) {
        //     abort(403, 'Invalid state');
        // }
        $request->session()->pull('oauth_state'); // discard stored state

        $code = $request->input('code');
        if (! $code) {
            abort(400, 'Missing authorization code');
        }

        $service = config('services.aisite');
        $http    = new Client(['timeout' => 10]);

        // --- Exchange authorization code for access token ---
        // POST http://127.0.0.1:8000/api/oauth/token
        $tokenResponse = $http->post(rtrim($service['base_url'], '/') . '/api/oauth/token', [
            'form_params' => [
                'grant_type'    => 'authorization_code',
                'client_id'     => $service['client_id'],
                'client_secret' => $service['client_secret'],
                'redirect_uri'  => $service['redirect'],
                'code'          => $code,
            ],
        ]);

        $tokenData   = json_decode((string) $tokenResponse->getBody(), true);
        $accessToken = $tokenData['access_token'] ?? null;

        if (! $accessToken) {
            abort(500, 'Failed to get access token from AISITE');
        }

        // --- Fetch user profile from AISITE ---
        // GET http://127.0.0.1:8000/api/user
        $userResponse = $http->get(rtrim($service['base_url'], '/') . '/api/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept'        => 'application/json',
            ],
        ]);

        $providerUser = json_decode((string) $userResponse->getBody(), true);

        // --- Find or create local user ---
        // Match by provider_id first, then fall back to email
        $query = User::query();

        if (! empty($providerUser['id'])) {
            $query->where('provider_id', $providerUser['id']);
        }
        if (! empty($providerUser['email'])) {
            $query->orWhere('email', $providerUser['email']);
        }

        $user = $query->first();

        if (! $user) {
            $user = User::create([
                'name'        => $providerUser['name']  ?? ($providerUser['email'] ?? 'AISITE User'),
                'email'       => $providerUser['email'] ?? null,
                'provider_id' => $providerUser['id']    ?? null,
                'password'    => bcrypt(Str::random(32)), // unusable password
            ]);
        } else {
            // Backfill provider_id if missing (e.g. user was matched by email)
            if (empty($user->provider_id) && ! empty($providerUser['id'])) {
                $user->provider_id = $providerUser['id'];
                $user->save();
            }
        }

        Auth::login($user, true); // true = "remember me"

        // Store the access token in session for later API calls if needed
        session(['aisite_access_token' => $accessToken]);

        return redirect()->route('dashboard');
    }
}
```

### 3.8 Routes

`routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\OAuthClientController;

// Home / welcome page
Route::get('/', function () {
    return view('welcome');
});

// /login redirects to AISITE OAuth — overrides Laravel's default login route
Route::get('/login', function () {
    return redirect()->route('login.aisite');
})->name('login');

// Step 1 — redirect browser to AISITE's /oauth/authorize
Route::get('/login/aisite', [OAuthClientController::class, 'redirectToProvider'])
    ->name('login.aisite');

// Step 2 — AISITE sends the browser back here with ?code=...
Route::get('/oauth/callback', [OAuthClientController::class, 'handleProviderCallback'])
    ->name('oauth.callback');

// Protected dashboard — only accessible after login
Route::middleware('auth')->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Logout
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
})->name('logout');
```

The `/login` route is named `login` — Laravel's `auth` middleware automatically redirects unauthenticated users to the `login` named route, so this is all that is needed to protect pages with `middleware('auth')`.

---

## 4. Full Authentication Flow — Step by Step

```
User (browser)              oauth-client               AISITE
     │                          │                        │
     │  GET /login              │                        │
     │─────────────────────────►│                        │
     │                          │                        │
     │  302 → /login/aisite     │                        │
     │◄─────────────────────────│                        │
     │                          │                        │
     │  GET /login/aisite       │                        │
     │─────────────────────────►│                        │
     │                          │  (builds redirect URL) │
     │  302 → AISITE/oauth/authorize?                    │
     │         response_type=code                        │
     │         &client_id=client_r1tk...                 │
     │         &redirect_uri=http://localhost:8001/oauth/callback
     │         &state=Abc123...                          │
     │◄─────────────────────────│                        │
     │                                                   │
     │  GET /oauth/authorize?...                         │
     │──────────────────────────────────────────────────►│
     │                                                   │
     │  (user not logged in to AISITE → shows login UI)  │
     │◄──────────────────────────────────────────────────│
     │                                                   │
     │  User submits email + password                    │
     │──────────────────────────────────────────────────►│
     │                                                   │
     │  (AISITE validates credentials, creates session)  │
     │                                                   │
     │  (AISITE runs /oauth/authorize logic:)            │
     │   - validates client_id, is_active                │
     │   - validates redirect_uri is allowed             │
     │   - creates oauth_authorization_codes row         │
     │     code=Xyz789... expires in 10 minutes          │
     │                                                   │
     │  302 → http://localhost:8001/oauth/callback       │
     │         ?code=Xyz789...&state=Abc123...           │
     │◄──────────────────────────────────────────────────│
     │                                                   │
     │  GET /oauth/callback?code=Xyz789...               │
     │─────────────────────────►│                        │
     │                          │                        │
     │                          │  POST /api/oauth/token │
     │                          │   grant_type=authorization_code
     │                          │   client_id=client_r1tk...
     │                          │   client_secret=AVqn...
     │                          │   redirect_uri=http://localhost:8001/oauth/callback
     │                          │   code=Xyz789...       │
     │                          │───────────────────────►│
     │                          │                        │
     │                          │   (AISITE validates,   │
     │                          │    marks code used,    │
     │                          │    issues Sanctum token)
     │                          │                        │
     │                          │◄───────────────────────│
     │                          │  { "access_token": "1|abc...",
     │                          │    "token_type": "Bearer",
     │                          │    "expires_in": 3600 }│
     │                          │                        │
     │                          │  GET /api/user         │
     │                          │  Authorization: Bearer 1|abc...
     │                          │───────────────────────►│
     │                          │                        │
     │                          │◄───────────────────────│
     │                          │  { "id": 42,           │
     │                          │    "name": "John Doe", │
     │                          │    "email": "j@ex.com"}│
     │                          │                        │
     │                          │  (find/create local user)
     │                          │  (Auth::login($user))  │
     │                          │                        │
     │  302 → /dashboard        │                        │
     │◄─────────────────────────│                        │
     │                          │                        │
     │  GET /dashboard          │                        │
     │─────────────────────────►│                        │
     │  200 OK (dashboard view) │                        │
     │◄─────────────────────────│                        │
```

---

## 5. Quick-Start Checklist

### On AISITE (the server)

- [ ] Run `php artisan migrate` to create `oauth_applications` and `oauth_authorization_codes` tables.
- [ ] Log in to AISITE at `http://127.0.0.1:8000`.
- [ ] Go to `/oauth/applications/create`.
- [ ] Create a new application with:
  - Name: anything descriptive
  - Redirect URI: `http://localhost:8001/oauth/callback` (exactly, one per line)
- [ ] Copy the generated **Client ID** and **Client Secret**.
- [ ] Start AISITE: `php artisan serve` (runs on port 8000 by default).

### On oauth-client (the client)

- [ ] `composer create-project laravel/laravel oauth-client "^10.0"`
- [ ] `composer require laravel/sanctum:^3.3 guzzlehttp/guzzle:^7.2`
- [ ] Add `provider_id` column to `create_users_table.php` migration.
- [ ] Run `php artisan migrate`.
- [ ] Set up `.env` with the four `AISITE_OAUTH_*` variables (using credentials from AISITE).
- [ ] Add the `aisite` block to `config/services.php`.
- [ ] Add `provider_id` to `User::$fillable`.
- [ ] Create `OAuthClientController.php` with `redirectToProvider()` and `handleProviderCallback()`.
- [ ] Register routes in `routes/web.php` (`/login`, `/login/aisite`, `/oauth/callback`, `/dashboard`, `/logout`).
- [ ] Start the client: `php artisan serve --port=8001`.
- [ ] Test: visit `http://localhost:8001/login` — should redirect to AISITE login.
