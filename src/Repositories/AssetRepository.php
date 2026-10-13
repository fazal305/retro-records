<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class AssetRepository
{
    public const ENVIRONMENTS = ['production', 'staging', 'development'];
    public const STATUSES = ['online', 'maintenance', 'offline'];
    public const SORTS = [
        'updated' => 'updated_at desc, id desc',
        'hostname' => 'lower(hostname) asc, id asc',
        'status' => 'status asc, lower(hostname) asc',
        'environment' => 'environment asc, lower(hostname) asc',
    ];
    public const PER_USER_LIMIT = 500;

    private const COLUMNS = 'id, hostname, host(ip_address) as ip_address, location, environment, status, notes, created_at, updated_at';

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function paginate(int $ownerId, array $filters, int $page, int $perPage): array
    {
        [$where, $params] = $this->filterClause($ownerId, $filters);
        $order = self::SORTS[$filters['sort'] ?? ''] ?? self::SORTS['updated'];

        $count = $this->pdo->prepare("select count(*) from assets where $where");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $stmt = $this->pdo->prepare('select ' . self::COLUMNS . " from assets where $where order by $order limit ? offset ?");
        $i = 1;
        foreach ($params as $value) {
            $stmt->bindValue($i++, $value);
        }
        $stmt->bindValue($i++, $perPage, PDO::PARAM_INT);
        $stmt->bindValue($i, ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();

        return ['rows' => $stmt->fetchAll(), 'total' => $total];
    }

    public function statusCounts(int $ownerId): array
    {
        $stmt = $this->pdo->prepare('select status, count(*) as n from assets where owner_id = ? group by status');
        $stmt->execute([$ownerId]);
        $counts = array_fill_keys(self::STATUSES, 0);
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['n'];
        }
        return $counts;
    }

    public function findOwned(int $id, int $ownerId): ?array
    {
        $stmt = $this->pdo->prepare('select ' . self::COLUMNS . ' from assets where id = ? and owner_id = ?');
        $stmt->execute([$id, $ownerId]);
        return $stmt->fetch() ?: null;
    }

    public function hostnameExists(int $ownerId, string $hostname, ?int $exceptId = null): bool
    {
        $stmt = $this->pdo->prepare('select 1 from assets where owner_id = ? and lower(hostname) = lower(?) and id <> ?');
        $stmt->execute([$ownerId, $hostname, $exceptId ?? 0]);
        return (bool) $stmt->fetchColumn();
    }

    public function countOwned(int $ownerId): int
    {
        $stmt = $this->pdo->prepare('select count(*) from assets where owner_id = ?');
        $stmt->execute([$ownerId]);
        return (int) $stmt->fetchColumn();
    }

    public function create(int $ownerId, array $data): int
    {
        $stmt = $this->pdo->prepare(
            'insert into assets (owner_id, hostname, ip_address, location, environment, status, notes)
             values (?, ?, ?, ?, ?, ?, ?) returning id'
        );
        $stmt->execute([
            $ownerId, $data['hostname'], $data['ip_address'], $data['location'],
            $data['environment'], $data['status'], $data['notes'],
        ]);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, int $ownerId, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'update assets set hostname = ?, ip_address = ?, location = ?, environment = ?, status = ?, notes = ?, updated_at = now()
             where id = ? and owner_id = ?'
        );
        $stmt->execute([
            $data['hostname'], $data['ip_address'], $data['location'],
            $data['environment'], $data['status'], $data['notes'], $id, $ownerId,
        ]);
        return $stmt->rowCount() === 1;
    }

    public function delete(int $id, int $ownerId): bool
    {
        $stmt = $this->pdo->prepare('delete from assets where id = ? and owner_id = ?');
        $stmt->execute([$id, $ownerId]);
        return $stmt->rowCount() === 1;
    }

    private function filterClause(int $ownerId, array $filters): array
    {
        $where = ['owner_id = ?'];
        $params = [$ownerId];

        if (($filters['q'] ?? '') !== '') {
            $like = '%' . addcslashes($filters['q'], '%_\\') . '%';
            $where[] = "(hostname ilike ? or location ilike ? or coalesce(host(ip_address), '') ilike ?)";
            array_push($params, $like, $like, $like);
        }
        if (in_array($filters['environment'] ?? '', self::ENVIRONMENTS, true)) {
            $where[] = 'environment = ?';
            $params[] = $filters['environment'];
        }
        if (in_array($filters['status'] ?? '', self::STATUSES, true)) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }

        return [implode(' and ', $where), $params];
    }
}
