<?php

namespace App\Providers;

use App\Services\SiteSettingsService;
use App\Services\UserDataService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private ?array $cachedGitMeta = null;

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

        // Share site settings with all views (fetched once, cached 30 min)
        View::share('siteSettings', (new SiteSettingsService())->getSettings());

        // Share user data with sidebar and topbar
        View::composer(['layouts.sidebar', 'layouts.topbar'], function ($view) {
            $userData = null;
            $accessToken = session('aisite_access_token');
            $role = session('aisite_user_role');
            $gitMeta = [];

            if ($accessToken) {
                $userDataService = new UserDataService();
                $userData = $userDataService->getUserDetails($accessToken);
                if ($role === null && is_array($userData)) {
                    $role = $userData['role'] ?? null;
                }

                // Log for debugging
                Log::info('User data fetched for view', [
                    'has_token' => !empty($accessToken),
                    'has_data' => !is_null($userData),
                    'data' => $userData,
                ]);
            }

            if ($this->isAdminRole($role)) {
                $gitMeta = $this->getGitMeta();
            }

            $view->with('userData', $userData);
            $view->with('gitMeta', $gitMeta);
        });
    }

    private function isAdminRole(mixed $role): bool
    {
        if (is_string($role)) {
            return strtolower(trim($role)) === 'admin';
        }

        if (is_array($role)) {
            foreach ($role as $value) {
                if (is_string($value) && strtolower(trim($value)) === 'admin') {
                    return true;
                }
            }
        }

        return false;
    }

    private function getGitMeta(): array
    {
        if (is_array($this->cachedGitMeta)) {
            return $this->cachedGitMeta;
        }

        $fullHash = $this->resolveCurrentGitHash();
        $shortHash = $fullHash ? substr($fullHash, 0, 7) : null;
        $commitUrl = $fullHash ? $this->buildCommitUrl($fullHash) : null;

        $this->cachedGitMeta = [
            'full_hash' => $fullHash,
            'short_hash' => $shortHash,
            'commit_url' => $commitUrl,
        ];

        return $this->cachedGitMeta;
    }

    private function resolveCurrentGitHash(): ?string
    {
        $envHash = $this->resolveHashFromEnvironment();
        if ($envHash !== null) {
            return $envHash;
        }

        $gitDir = $this->resolveGitDirectory();
        if ($gitDir === null) {
            return null;
        }

        $headFile = $gitDir . DIRECTORY_SEPARATOR . 'HEAD';

        if (!is_file($headFile)) {
            return null;
        }

        $headContent = trim((string) file_get_contents($headFile));
        if ($headContent === '') {
            return null;
        }

        if (str_starts_with($headContent, 'ref: ')) {
            $ref = trim(substr($headContent, 5));
            $refFile = $gitDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ref);

            if (is_file($refFile)) {
                $hash = trim((string) file_get_contents($refFile));
            } else {
                $hash = $this->resolvePackedRefHash($ref, $gitDir);
            }
        } else {
            $hash = $headContent;
        }

        return $this->normalizeHash($hash);
    }

    private function resolvePackedRefHash(string $ref, string $gitDir): ?string
    {
        $packedRefsFile = $gitDir . DIRECTORY_SEPARATOR . 'packed-refs';
        if (!is_file($packedRefsFile)) {
            return null;
        }

        $lines = file($packedRefsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return null;
        }

        foreach ($lines as $line) {
            if (str_starts_with($line, '#') || str_starts_with($line, '^')) {
                continue;
            }

            $parts = preg_split('/\s+/', trim($line));
            if (!is_array($parts) || count($parts) < 2) {
                continue;
            }

            if ($parts[1] === $ref) {
                return $parts[0];
            }
        }

        return null;
    }

    private function buildCommitUrl(string $fullHash): ?string
    {
        $gitDir = $this->resolveGitDirectory();
        if ($gitDir === null) {
            return null;
        }

        $configFile = $gitDir . DIRECTORY_SEPARATOR . 'config';
        if (!is_file($configFile)) {
            return null;
        }

        $config = (string) file_get_contents($configFile);
        if ($config === '') {
            return null;
        }

        if (!preg_match('/\[remote "origin"\][\s\S]*?url\s*=\s*(.+)/', $config, $matches)) {
            return null;
        }

        $remoteUrl = trim($matches[1]);
        $webBase = $this->normalizeRemoteToWebBase($remoteUrl);
        if (!$webBase) {
            return null;
        }

        if (str_contains($webBase, 'bitbucket.org')) {
            return $webBase . '/commits/' . $fullHash;
        }

        return $webBase . '/commit/' . $fullHash;
    }

    private function normalizeRemoteToWebBase(string $remoteUrl): ?string
    {
        $remoteUrl = trim($remoteUrl);
        if ($remoteUrl === '') {
            return null;
        }

        if (str_starts_with($remoteUrl, 'git@')) {
            $withoutPrefix = substr($remoteUrl, 4);
            $parts = explode(':', $withoutPrefix, 2);
            if (count($parts) !== 2) {
                return null;
            }

            [$host, $path] = $parts;
            $remoteUrl = 'https://' . $host . '/' . $path;
        } elseif (str_starts_with($remoteUrl, 'ssh://')) {
            $remoteUrl = preg_replace('#^ssh://git@#', 'https://', $remoteUrl);
        }

        $remoteUrl = preg_replace('#\.git$#', '', $remoteUrl);
        if (!is_string($remoteUrl) || !preg_match('#^https?://#', $remoteUrl)) {
            return null;
        }

        return rtrim($remoteUrl, '/');
    }

    private function resolveGitDirectory(): ?string
    {
        $gitPath = base_path('.git');

        if (is_dir($gitPath)) {
            return $gitPath;
        }

        // Support deployments/worktrees where .git is a file: "gitdir: /actual/path"
        if (is_file($gitPath)) {
            $content = trim((string) file_get_contents($gitPath));
            if (preg_match('/^gitdir:\s*(.+)$/i', $content, $matches)) {
                $gitDir = trim($matches[1]);
                if ($gitDir !== '' && is_dir($gitDir)) {
                    return $gitDir;
                }
            }
        }

        return null;
    }

    private function resolveHashFromEnvironment(): ?string
    {
        $keys = [
            'APP_COMMIT_HASH',
            'GIT_COMMIT_HASH',
            'GIT_COMMIT',
            'COMMIT_SHA',
            'CI_COMMIT_SHA',
            'RENDER_GIT_COMMIT',
            'VERCEL_GIT_COMMIT_SHA',
        ];

        foreach ($keys as $key) {
            $val = env($key);
            $hash = $this->normalizeHash($val);
            if ($hash !== null) {
                return $hash;
            }
        }

        return null;
    }

    private function normalizeHash(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $hash = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{40}$/', $hash)) {
            return $hash;
        }

        return null;
    }
}
