<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Middleware;
use App\Http\Request;
use App\Http\Response;

final class GuestOnly implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        return $request->user !== null ? Response::redirect('/assets') : $next($request);
    }
}
