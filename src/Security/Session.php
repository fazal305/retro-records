<?php

declare(strict_types=1);

namespace App\Security;

final class Session
{
    public const IDLE_TIMEOUT = 900;
    public const ABSOLUTE_TIMEOUT = 43200;
    public const COOKIE = '__Host-rrsid';

    private static bool $expired = false;

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.lazy_write', '1');
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '50');
        ini_set('session.cache_limiter', '');

        session_set_save_handler(new DbSessionHandler(), false);
        session_name(self::COOKIE);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();

        $now = time();
        $lastSeen = $_SESSION['_last_seen'] ?? $now;
        $startedAt = $_SESSION['_started_at'] ?? $now;
        if ($now - $lastSeen > self::IDLE_TIMEOUT || $now - $startedAt > self::ABSOLUTE_TIMEOUT) {
            self::$expired = isset($_SESSION['user_id']);
            $_SESSION = [];
            session_regenerate_id(true);
            $startedAt = $now;
        }
        $_SESSION['_last_seen'] = $now;
        $_SESSION['_started_at'] = $startedAt;
    }

    public static function wasExpired(): bool
    {
        return self::$expired;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function pullFlashes(): array
    {
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    public static function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['_started_at'] = time();
        Csrf::rotate();
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
        Csrf::rotate();
    }

    public static function userId(): ?int
    {
        $id = $_SESSION['user_id'] ?? null;
        return is_int($id) ? $id : null;
    }
}
