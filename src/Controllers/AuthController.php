<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\UserRepository;
use App\Security\PasswordHasher;
use App\Security\Session;
use App\Validation\Validator;
use PDOException;

final class AuthController
{
    public const MAX_ATTEMPTS = 5;
    public const LOCK_SECONDS = 900;

    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function showLogin(Request $request): Response
    {
        return Response::view('auth/login', ['title' => 'Sign in', 'nav' => 'login', 'identifier' => '']);
    }

    public function login(Request $request): Response
    {
        $identifier = mb_substr($request->input('identifier'), 0, 254);
        $password = mb_substr($request->raw('password'), 0, 256);

        $errors = [];
        if ($identifier === '') {
            $errors['identifier'] = 'Enter your username or email.';
        }
        if ($password === '') {
            $errors['password'] = 'Enter your password.';
        }
        if ($errors) {
            return Response::view('auth/login', ['title' => 'Sign in', 'nav' => 'login', 'identifier' => $identifier, 'errors' => $errors], 422);
        }

        $user = $this->users->findForLogin($identifier);
        if ($user === null) {
            PasswordHasher::burn($password);
            return $this->failedLogin($identifier);
        }

        $valid = PasswordHasher::verify($password, $user['password_hash']);
        if ($user['locked']) {
            return $this->failedLogin($identifier);
        }
        if (!$valid) {
            $this->users->recordFailedLogin($user['id'], self::MAX_ATTEMPTS, self::LOCK_SECONDS);
            return $this->failedLogin($identifier);
        }

        if (PasswordHasher::needsRehash($user['password_hash'])) {
            $this->users->updatePassword($user['id'], PasswordHasher::hash($password));
        }
        $this->users->recordSuccessfulLogin($user['id']);

        $intended = Session::get('_intended', '/assets');
        Session::login($user['id']);
        Session::flash('ok', 'Signed in. Welcome back.');
        return Response::redirect(is_string($intended) ? $intended : '/assets');
    }

    public function showRegister(Request $request): Response
    {
        return Response::view('auth/register', ['title' => 'Create account', 'nav' => 'register', 'old' => []]);
    }

    public function register(Request $request): Response
    {
        [$data, $errors] = Validator::registration([
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => $request->raw('password'),
            'password_confirmation' => $request->raw('password_confirmation'),
            'consent' => $request->input('consent'),
        ]);

        if (!isset($errors['username']) && $this->users->usernameTaken($data['username'])) {
            $errors['username'] = 'That username is already taken.';
        }
        if (!isset($errors['email']) && $this->users->emailTaken($data['email'])) {
            $errors['email'] = 'An account with that email already exists. Try signing in instead.';
        }
        if ($errors) {
            return Response::view('auth/register', ['title' => 'Create account', 'nav' => 'register', 'old' => $data, 'errors' => $errors], 422);
        }

        try {
            $id = $this->users->create($data['username'], $data['email'], PasswordHasher::hash($request->raw('password')));
        } catch (PDOException $e) {
            if ($e->getCode() !== '23505') {
                throw $e;
            }
            $errors['username'] = 'That username or email was just taken. Try another.';
            return Response::view('auth/register', ['title' => 'Create account', 'nav' => 'register', 'old' => $data, 'errors' => $errors], 422);
        }

        Session::login($id);
        Session::flash('ok', 'Account created. Add your first server below.');
        return Response::redirect('/assets');
    }

    public function logout(Request $request): Response
    {
        Session::logout();
        Session::flash('ok', 'You have been signed out.');
        return Response::redirect('/login');
    }

    private function failedLogin(string $identifier): Response
    {
        return Response::view('auth/login', [
            'title' => 'Sign in',
            'identifier' => $identifier,
            'failed' => true,
        ], 401);
    }
}
