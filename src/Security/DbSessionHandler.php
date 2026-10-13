<?php

declare(strict_types=1);

namespace App\Security;

use App\Database;
use SessionHandlerInterface;
use SessionIdInterface;
use SessionUpdateTimestampHandlerInterface;

final class DbSessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface
{
    private const RETENTION = '24 hours';

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function create_sid(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function validateId(string $id): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $id)) {
            return false;
        }
        $stmt = Database::pdo()->prepare(
            "select 1 from sessions where id = ? and last_activity > now() - interval '" . self::RETENTION . "'"
        );
        $stmt->execute([$id]);
        return (bool) $stmt->fetchColumn();
    }

    public function read(string $id): string|false
    {
        $stmt = Database::pdo()->prepare(
            "select data from sessions where id = ? and last_activity > now() - interval '" . self::RETENTION . "'"
        );
        $stmt->execute([$id]);
        $data = $stmt->fetchColumn();
        return $data === false ? '' : (string) $data;
    }

    public function write(string $id, string $data): bool
    {
        $stmt = Database::pdo()->prepare(
            'insert into sessions (id, data, last_activity) values (?, ?, now())
             on conflict (id) do update set data = excluded.data, last_activity = now()'
        );
        return $stmt->execute([$id, $data]);
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        return Database::pdo()->prepare('update sessions set last_activity = now() where id = ?')->execute([$id]);
    }

    public function destroy(string $id): bool
    {
        return Database::pdo()->prepare('delete from sessions where id = ?')->execute([$id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $pdo = Database::pdo();
        $deleted = $pdo->exec("delete from sessions where last_activity < now() - interval '" . self::RETENTION . "'");
        $pdo->exec("delete from rate_hits where hit_at < now() - interval '1 day'");
        return $deleted;
    }
}
