<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Serves public GET pages from the file cache so a page view needs no database connection.
 * Hostinger caps the MySQL user at 500 connections an hour; one crawl used to take the site down.
 * The cache is keyed by deploy, so every deploy starts fresh.
 */
class PageCacheMiddleware
{
    /** Pages that carry a CSRF token or per-visitor content. */
    private const SKIP_PREFIXES = [
        '/contact-us/', '/career/', '/request-a-quote/', '/id/request-a-quote/',
        '/toolkit/ejma-bellows-calculator/', '/inquiries/', '/admin', '/livewire',
    ];

    private const CACHEABLE_STATUSES = [200, 301, 404, 410];

    public function handle(Request $request, Closure $next)
    {
        if (! $this->cacheable($request)) {
            return $next($request);
        }

        $store = Cache::store();
        $key = 'page:' . self::version() . ':' . sha1($request->getSchemeAndHttpHost() . $request->getPathInfo());

        if ($hit = $store->get($key)) {
            return response($hit['body'], $hit['status'], $hit['headers'] + ['X-Page-Cache' => 'HIT']);
        }

        $response = $next($request);

        if (in_array($response->getStatusCode(), self::CACHEABLE_STATUSES, true)) {
            $headers = array_filter([
                'Content-Type' => $response->headers->get('Content-Type'),
                'Location' => $response->headers->get('Location'),
            ]);

            $store->put($key, [
                'status' => $response->getStatusCode(),
                'headers' => $headers,
                'body' => $response->getContent(),
            ], (int) config('cache.page_ttl'));
        }

        return $response;
    }

    private function cacheable(Request $request): bool
    {
        if ((int) config('cache.page_ttl') <= 0 || ! $request->isMethod('GET') || $request->getQueryString()) {
            return false;
        }

        // Flash messages (form success / validation errors) are per visitor.
        if ($request->hasSession() && ! empty($request->session()->get('_flash.old'))) {
            return false;
        }

        $path = rtrim($request->getPathInfo(), '/') . '/';
        foreach (self::SKIP_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return false;
            }
        }

        return true;
    }

    /** Changes on every deploy: `artisan optimize` rewrites the cached config. */
    private static function version(): string
    {
        $file = base_path('bootstrap/cache/config.php');

        return is_file($file) ? (string) filemtime($file) : 'dev';
    }
}
