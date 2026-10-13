<?php

declare(strict_types=1);

namespace App;

use PDO;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
                Config::require('DB_HOST'),
                Config::get('DB_PORT', '5432'),
                Config::get('DB_NAME', 'postgres'),
                Config::get('DB_SSLMODE', 'require'),
            );
            self::$pdo = new PDO($dsn, Config::require('DB_USER'), Config::require('DB_PASSWORD'), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 10,
            ]);
            self::$pdo->exec("set search_path = retro_records");
        }
        return self::$pdo;
    }

    public static function actAs(?int $userId): void
    {
        self::pdo()->prepare("select set_config('app.user_id', ?, false)")
            ->execute([$userId === null ? '' : (string) $userId]);
    }
}
