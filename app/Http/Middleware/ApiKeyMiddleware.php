<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = config('app.internal_api_key');
        $providedKey   = $request->header('X-Internal-Api-Key')
                      ?? $request->query('api_key');

        if (empty($configuredKey) || $providedKey !== $configuredKey) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
