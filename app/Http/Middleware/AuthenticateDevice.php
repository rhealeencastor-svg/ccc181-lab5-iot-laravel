<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('iot.device_token', '');
        $provided = (string) $request->bearerToken();

        if ($expected === '' || ! hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized device.'], 401);
        }

        return $next($request);
    }
}
