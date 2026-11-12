<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BasicAuthApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedUsername = (string) config('services.dokter_api.username');
        $expectedPassword = (string) config('services.dokter_api.password');

        $valid = filled($expectedUsername) && filled($expectedPassword)
            && hash_equals($expectedUsername, (string) $request->getUser())
            && hash_equals($expectedPassword, (string) $request->getPassword());

        if (! $valid) {
            return response()->json(['message' => 'Unauthorized.'], 401)
                ->header('WWW-Authenticate', 'Basic');
        }

        return $next($request);
    }
}
