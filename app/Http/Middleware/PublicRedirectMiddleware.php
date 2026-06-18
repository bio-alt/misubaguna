<?php

namespace App\Http\Middleware;

use App\Models\PublicSite\PublicRedirect;
use Closure;
use Illuminate\Http\Request;

class PublicRedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $path = $request->getPathInfo();

        $redirect = PublicRedirect::where('source_path', $path)
            ->where('is_active', true)
            ->first();

        if ($redirect) {
            $redirect->increment('hit_count');
            $redirect->update(['last_hit_at' => now()]);

            return redirect($redirect->target_url, $redirect->status_code);
        }

        return $next($request);
    }
}