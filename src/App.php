<?php

declare(strict_types=1);

namespace App;

use App\Controllers\AccountController;
use App\Controllers\AdminController;
use App\Controllers\AssetController;
use App\Controllers\AuthController;
use App\Controllers\PageController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\GuestOnly;
use App\Http\Middleware\HandleErrors;
use App\Http\Middleware\RequireAdmin;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\StartSession;
use App\Http\Middleware\Throttle;
use App\Http\Middleware\VerifyCsrf;
use App\Http\Request;
use App\Http\Router;

final class App
{
    public static function router(): Router
    {
        $router = new Router();
        $router->use(
            new SecurityHeaders(),
            new HandleErrors(),
            new StartSession(),
            new Throttle('mutation', 120, 600),
            new VerifyCsrf(),
        );

        $auth = [new Authenticate()];
        $guest = [new GuestOnly()];
        $passwordCheck = [new Authenticate(), new Throttle('password', 10, 900)];

        $pages = new PageController();
        $router->get('/', $pages->home(...));
        $router->get('/privacy', $pages->privacy(...));
        $router->get('/terms', $pages->terms(...));

        $authC = new AuthController();
        $router->get('/login', $authC->showLogin(...), $guest);
        $router->post('/login', $authC->login(...), [new GuestOnly(), new Throttle('login', 20, 900)]);
        $router->get('/register', $authC->showRegister(...), $guest);
        $router->post('/register', $authC->register(...), [new GuestOnly(), new Throttle('register', 5, 3600)]);
        $router->post('/logout', $authC->logout(...), $auth);

        $assets = new AssetController();
        $router->get('/assets', $assets->index(...), $auth);
        $router->get('/assets/new', $assets->create(...), $auth);
        $router->post('/assets', $assets->store(...), $auth);
        $router->get('/assets/{id}/edit', $assets->edit(...), $auth);
        $router->put('/assets/{id}', $assets->update(...), $auth);
        $router->get('/assets/{id}/delete', $assets->confirmDelete(...), $auth);
        $router->delete('/assets/{id}', $assets->destroy(...), $auth);

        $account = new AccountController();
        $router->get('/account', $account->show(...), $auth);
        $router->put('/account/password', $account->updatePassword(...), $passwordCheck);
        $router->delete('/account', $account->destroy(...), $passwordCheck);

        $admin = new AdminController();
        $adminOnly = [new Authenticate(), new RequireAdmin()];
        $router->get('/admin/users', $admin->users(...), $adminOnly);
        $router->post('/admin/users/{id}/unlock', $admin->unlock(...), $adminOnly);

        return $router;
    }

    public static function run(): void
    {
        self::router()->dispatch(Request::fromGlobals())->send();
    }
}
