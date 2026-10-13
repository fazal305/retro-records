<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class UserRepository
{
    private const COLUMNS = 'id, username, email, role, failed_logins, locked_until, last_login_at, created_at';

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('select ' . self::COLUMNS . ' from users where id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findForLogin(string $identifier): ?array
    {
        $stmt = $this->pdo->prepare(
            'select id, password_hash, locked_until > now() as locked from users where lower(username) = lower(?) or lower(email) = lower(?) limit 1'
        );
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch() ?: null;
    }

    public function passwordHash(int $id): ?string
    {
        $stmt = $this->pdo->prepare('select password_hash from users where id = ?');
        $stmt->execute([$id]);
        $hash = $stmt->fetchColumn();
        return $hash === false ? null : $hash;
    }

    public function usernameTaken(string $username): bool
    {
        $stmt = $this->pdo->prepare('select 1 from users where lower(username) = lower(?)');
        $stmt->execute([$username]);
        return (bool) $stmt->fetchColumn();
    }

    public function emailTaken(string $email): bool
    {
        $stmt = $this->pdo->prepare('select 1 from users where lower(email) = lower(?)');
        $stmt->execute([$email]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(string $username, string $email, string $passwordHash): int
    {
        $stmt = $this->pdo->prepare('insert into users (username, email, password_hash) values (?, ?, ?) returning id');
        $stmt->execute([$username, $email, $passwordHash]);
        return (int) $stmt->fetchColumn();
    }

    public function recordFailedLogin(int $id, int $maxAttempts, int $lockSeconds): void
    {
        $stmt = $this->pdo->prepare(
            'update users set
                locked_until = case when failed_logins + 1 >= ? then now() + make_interval(secs => ?) else locked_until end,
                failed_logins = case when failed_logins + 1 >= ? then 0 else failed_logins + 1 end
             where id = ?'
        );
        $stmt->execute([$maxAttempts, $lockSeconds, $maxAttempts, $id]);
    }

    public function recordSuccessfulLogin(int $id): void
    {
        $this->pdo->prepare('update users set failed_logins = 0, locked_until = null, last_login_at = now() where id = ?')
            ->execute([$id]);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $this->pdo->prepare('update users set password_hash = ? where id = ?')->execute([$hash, $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('delete from users where id = ?')->execute([$id]);
    }

    public function unlock(int $id): bool
    {
        $stmt = $this->pdo->prepare('update users set failed_logins = 0, locked_until = null where id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() === 1;
    }

    public function promote(string $username): bool
    {
        $stmt = $this->pdo->prepare("update users set role = 'admin' where lower(username) = lower(?)");
        $stmt->execute([$username]);
        return $stmt->rowCount() === 1;
    }

    public function paginateForAdmin(int $page, int $perPage): array
    {
        $total = (int) $this->pdo->query('select count(*) from users')->fetchColumn();
        $stmt = $this->pdo->prepare(
            'select ' . self::COLUMNS . ', locked_until > now() as locked from users order by created_at desc limit ? offset ?'
        );
        $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(2, ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();
        return ['rows' => $stmt->fetchAll(), 'total' => $total];
    }
}
