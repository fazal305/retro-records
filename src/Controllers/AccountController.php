<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\UserRepository;
use App\Security\PasswordHasher;
use App\Security\Session;
use App\Validation\Validator;

final class AccountController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function show(Request $request, array $errors = [], int $status = 200): Response
    {
        return Response::view('account', ['title' => 'My account', 'nav' => 'account', 'errors' => $errors], $status);
    }

    public function updatePassword(Request $request): Response
    {
        $user = $request->user;
        $errors = [];

        if (!PasswordHasher::verify($request->raw('current_password'), (string) $this->users->passwordHash($user['id']))) {
            $errors['current_password'] = 'Your current password is incorrect.';
        }
        $error = Validator::passwordError($request->raw('password'), $request->raw('password_confirmation'), $user['username']);
        if ($error !== null) {
            $errors['password'] = $error;
        }
        if ($errors) {
            return $this->show($request, $errors, 422);
        }

        $this->users->updatePassword($user['id'], PasswordHasher::hash($request->raw('password')));
        Session::login($user['id']);
        Session::flash('ok', 'Password changed.');
        return Response::redirect('/account');
    }

    public function destroy(Request $request): Response
    {
        $user = $request->user;
        if (!PasswordHasher::verify($request->raw('delete_password'), (string) $this->users->passwordHash($user['id']))) {
            return $this->show($request, ['delete_password' => 'Enter your current password to confirm.'], 422);
        }

        $this->users->delete($user['id']);
        Session::logout();
        Session::flash('ok', 'Your account and all of its servers have been deleted.');
        return Response::redirect('/login');
    }
}
