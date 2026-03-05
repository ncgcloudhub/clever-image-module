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
            $gitMeta = $this->getGitMeta();

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
            $view->with('gitMeta', $gitMeta);
        });
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
        $gitDir = base_path('.git');
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

        return preg_match('/^[0-9a-f]{40}$/i', $hash ?? '') ? strtolower($hash) : null;
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
        $configFile = base_path('.git' . DIRECTORY_SEPARATOR . 'config');
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
}
