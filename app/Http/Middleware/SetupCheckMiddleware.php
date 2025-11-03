<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetupCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $storageMode = config('tinyurl.storage_mode');

        // Skip setup check for setup routes, API health check, and assets
        if ($request->is('setup*') || $request->is('up') || $request->is('_*')) {
            return $next($request);
        }

        // If STORAGE_MODE is empty, redirect to setup
        if (empty($storageMode)) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}
