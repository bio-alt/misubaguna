<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrailingSlashMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $url = $request->getRequestUri();

        if ($url !== '/' && !str_ends_with($url, '/') && !str_contains($url, '.')) {
            return redirect(rtrim($url, '/') . '/', 301);
        }

        return $next($request);
    }
}