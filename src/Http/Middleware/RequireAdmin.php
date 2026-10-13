<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Middleware;
use App\Http\Request;
use App\Http\Response;

final class RequireAdmin implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (($request->user['role'] ?? null) !== 'admin') {
            return Response::error(403, 'You need an administrator account to open this page.');
        }
        return $next($request);
    }
}
