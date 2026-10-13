<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Database;
use App\Http\Middleware;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\UserRepository;
use App\Security\Session;
use App\View;

final class StartSession implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        Session::start();

        if (Session::wasExpired()) {
            Session::flash('warn', 'Your session expired after 15 minutes of inactivity. Please sign in again.');
            if ($request->isMutating()) {
                return Response::redirect('/login');
            }
        }

        $userId = Session::userId();
        if ($userId !== null) {
            $request->user = (new UserRepository())->find($userId);
            if ($request->user === null) {
                Session::logout();
            }
        }
        Database::actAs($request->user['id'] ?? null);
        View::share('user', $request->user);

        $response = $next($request);
        session_write_close();
        return $response;
    }
}
