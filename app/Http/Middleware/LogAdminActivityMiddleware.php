<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivityMiddleware
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log state-changing methods
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        // Only log successful outcomes (2xx or redirect after POST)
        $status = $response->getStatusCode();
        if ($status >= 400) {
            return $response;
        }

        try {
            ActivityLogger::fromRequest($request);
        } catch (\Throwable) {
            // Never let logging crash the app
        }

        return $response;
    }
}
