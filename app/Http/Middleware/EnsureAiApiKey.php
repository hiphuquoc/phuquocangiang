<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAiApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $configuredKey = (string) config('ai.api_key', '');

        // If no key configured, skip this guard for local bootstrap.
        if ($configuredKey === '') {
            return $next($request);
        }

        $requestKey = (string) ($request->header('X-AI-API-KEY') ?? '');
        if (!hash_equals($configuredKey, $requestKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized AI request.',
            ], 401);
        }

        return $next($request);
    }
}
