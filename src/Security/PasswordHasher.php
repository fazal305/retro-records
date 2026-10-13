<?php

declare(strict_types=1);

namespace App\Security;

final class PasswordHasher
{
    private const DUMMY_HASH = '$argon2id$v=19$m=65536,t=4,p=1$aUVaLmhYdDRNbkVuZkV4cQ$x+fv467DZzVZe8R5hTVlh3INtGwoaIdWXINocCBbV4I';

    public static function algorithm(): string
    {
        return defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
    }

    public static function hash(string $password): string
    {
        return password_hash($password, self::algorithm());
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, self::algorithm());
    }

    // Spends the same time as a real verify so unknown usernames can't be detected by timing.
    public static function burn(string $password): void
    {
        password_verify($password, self::DUMMY_HASH);
    }
}
