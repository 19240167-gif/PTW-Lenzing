<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks any development-auth route when APP_ENV=production or AUTH_MODE != development.
 * This is a hard guard — development login must never be accessible in production.
 */
class BlockDevelopmentAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment("production") || config("auth.mode") !== "development") {
            abort(404);
        }

        return $next($request);
    }
}
