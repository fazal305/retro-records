<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

if (PHP_SAPI !== 'cli' || !isset($argv[1])) {
    fwrite(STDERR, "Usage: php bin/promote-admin.php <username>\n");
    exit(1);
}

$ok = (new App\Repositories\UserRepository())->promote($argv[1]);
echo $ok ? "{$argv[1]} is now an administrator.\n" : "No user named {$argv[1]}.\n";
exit($ok ? 0 : 1);
