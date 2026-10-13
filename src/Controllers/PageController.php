<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

final class PageController
{
    public function home(Request $request): Response
    {
        if ($request->user !== null) {
            return Response::redirect('/assets');
        }
        return Response::view('home', ['title' => null]);
    }

    public function privacy(Request $request): Response
    {
        return Response::view('legal/privacy', ['title' => 'Privacy & cookies', 'nav' => 'privacy']);
    }

    public function terms(Request $request): Response
    {
        return Response::view('legal/terms', ['title' => 'Terms of use', 'nav' => 'terms']);
    }
}
