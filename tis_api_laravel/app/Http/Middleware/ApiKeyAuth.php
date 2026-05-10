<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('Authorization');

        if (!$apiKey || !str_starts_with($apiKey, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $key = str_replace('Bearer ', '', $apiKey);

        if ($key !== config('app.tis_api_key')) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        return $next($request);
    }
}
