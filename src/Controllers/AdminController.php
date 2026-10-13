<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\UserRepository;
use App\Security\Session;

final class AdminController
{
    public const PER_PAGE = 20;

    public function users(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', '1'));
        $result = (new UserRepository())->paginateForAdmin($page, self::PER_PAGE);

        return Response::view('admin/users', [
            'title' => 'User accounts',
            'nav' => 'admin',
            'users' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / self::PER_PAGE)),
        ]);
    }

    public function unlock(Request $request): Response
    {
        if (!(new UserRepository())->unlock($request->params['id'])) {
            return Response::error(404, 'That account no longer exists.');
        }
        Session::flash('ok', 'Account unlocked.');
        return Response::redirect('/admin/users');
    }
}
