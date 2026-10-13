<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Middleware;
use App\Http\Request;
use App\Http\Response;
use App\Security\Csrf;

final class VerifyCsrf implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!$request->isMutating()) {
            return $next($request);
        }

        $origin = $request->header('Origin');
        if ($origin !== null && $origin !== 'null' && parse_url($origin, PHP_URL_HOST) . $this->port($origin) !== $request->host()) {
            return Response::error(403, 'Cross-origin request blocked.');
        }

        $token = $request->input('_token') ?: $request->header('X-CSRF-Token');
        if (!Csrf::valid($token)) {
            return Response::error(419, 'This form expired or was submitted from another site. Go back, reload the page and try again.');
        }

        return $next($request);
    }

    private function port(string $origin): string
    {
        $port = parse_url($origin, PHP_URL_PORT);
        return $port ? ':' . $port : '';
    }
}
