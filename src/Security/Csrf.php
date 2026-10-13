<?php

declare(strict_types=1);

namespace App\Security;

final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf');
        if (!is_string($token)) {
            $token = self::rotate();
        }
        return $token;
    }

    public static function rotate(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set('_csrf', $token);
        return $token;
    }

    public static function valid(?string $submitted): bool
    {
        $expected = Session::get('_csrf');
        return is_string($expected) && is_string($submitted) && $submitted !== '' && hash_equals($expected, $submitted);
    }
}
