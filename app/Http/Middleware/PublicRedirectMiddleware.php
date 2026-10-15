<?php

namespace App\Http\Middleware;

use App\Models\PublicSite\PublicRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicRedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Loaded once into the cache (file store in production): querying MySQL on every request (robots.txt included)
        // ran the site into Hostinger's 500-connections-an-hour limit.
        $redirects = Cache::remember('public-redirects', 3600, fn () => PublicRedirect::where('is_active', true)
            ->get(['source_path', 'target_url', 'status_code'])
            ->keyBy('source_path')
            ->map(fn ($r) => [$r->target_url, $r->status_code])
            ->all());

        if ($redirect = $redirects[$request->getPathInfo()] ?? null) {
            return redirect($redirect[0], $redirect[1]);
        }

        return $next($request);
    }
}
