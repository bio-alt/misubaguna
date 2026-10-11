<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrailingSlashMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('testing')) {
            return $next($request);
        }

        // Only redirect safe requests; a 301 on a POST turns it into a GET and drops the form data.
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $path = $request->getPathInfo();
        $query = $request->getQueryString();

        if ($path !== '/' && !str_ends_with($path, '/') && !str_contains($path, '.')) {
            $absoluteUrl = $request->getSchemeAndHttpHost() . $path . '/' . ($query ? '?' . $query : '');
            return redirect()->to($absoluteUrl, 301);
        }

        return $next($request);
    }
}