<?php

declare(strict_types=1);

namespace App\Security;

use App\Config;
use App\Database;

final class RateLimiter
{
    public static function attempt(string $bucket, string $key, int $max, int $windowSeconds): bool
    {
        $pdo = Database::pdo();
        $hash = hash_hmac('sha256', $key, Config::require('APP_KEY'));

        $count = $pdo->prepare(
            'select count(*) from rate_hits where bucket = ? and key_hash = ? and hit_at > now() - make_interval(secs => ?)'
        );
        $count->execute([$bucket, $hash, $windowSeconds]);
        if ((int) $count->fetchColumn() >= $max) {
            return false;
        }

        $pdo->prepare('insert into rate_hits (bucket, key_hash) values (?, ?)')->execute([$bucket, $hash]);
        return true;
    }
}
