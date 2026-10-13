<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Middleware;
use App\Http\Request;
use App\Http\Response;

final class SecurityHeaders implements Middleware
{
    public const CSP = "default-src 'none'; script-src 'self'; style-src 'self'; img-src 'self' data:; "
        . "connect-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'; manifest-src 'self'";

    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);
        $response->headers += [
            'Content-Security-Policy' => self::CSP,
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'same-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Strict-Transport-Security' => 'max-age=63072000; includeSubDomains',
            'Cache-Control' => 'no-store',
        ];
        return $response;
    }
}
