<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrailingSlashMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $path = $request->getPathInfo();
        $query = $request->getQueryString();

        if ($path !== '/' && !str_ends_with($path, '/') && !str_contains($path, '.')) {
            $absoluteUrl = $request->getSchemeAndHttpHost() . $path . '/' . ($query ? '?' . $query : '');
            return redirect()->to($absoluteUrl, 301);
        }

        return $next($request);
    }
}