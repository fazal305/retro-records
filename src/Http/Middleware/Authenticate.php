<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Middleware;
use App\Http\Request;
use App\Http\Response;
use App\Security\Session;

final class Authenticate implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if ($request->user === null) {
            if (!Session::wasExpired()) {
                Session::flash('warn', 'Please sign in to continue.');
            }
            if (!$request->isMutating()) {
                Session::set('_intended', $request->fullUrl());
            }
            return Response::redirect('/login');
        }
        return $next($request);
    }
}
