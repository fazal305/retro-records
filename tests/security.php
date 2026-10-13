<?php

declare(strict_types=1);

// Usage: php tests/security.php [base-url]
// Runs against a local dev server and needs the same .env (it seeds and cleans up its own test users).

require __DIR__ . '/../src/bootstrap.php';

use App\Database;

$base = rtrim($argv[1] ?? 'http://localhost:8091', '/');
$failures = 0;

final class Client
{
    public array $cookies = [];

    public function __construct(private readonly string $base)
    {
    }

    public function request(string $method, string $path, array $form = [], array $headers = []): array
    {
        $ch = curl_init($this->base . $path);
        $cookie = implode('; ', array_map(fn ($k, $v) => "$k=$v", array_keys($this->cookies), $this->cookies));
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => array_merge($headers, $cookie ? ["Cookie: $cookie"] : []),
            CURLOPT_TIMEOUT => 60,
        ]);
        if ($form) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
        }
        $raw = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $rawHeaders = substr($raw, 0, $headerSize);
        preg_match_all('/^Set-Cookie:\s*([^=]+)=([^;]*)/mi', $rawHeaders, $m, PREG_SET_ORDER);
        foreach ($m as [, $name, $value]) {
            $this->cookies[$name] = $value;
        }
        return ['status' => $status, 'headers' => $rawHeaders, 'body' => substr($raw, $headerSize)];
    }

    public function token(string $path = '/login'): string
    {
        preg_match('/name="_token" value="([a-f0-9]{64})"/', $this->request('GET', $path)['body'], $m);
        return $m[1] ?? '';
    }

    public function post(string $path, array $form, string $tokenFrom = '/login', array $headers = []): array
    {
        return $this->request('POST', $path, $form + ['_token' => $this->token($tokenFrom)], $headers);
    }
}

function check(string $label, bool $ok, string $detail = ''): void
{
    global $failures;
    $failures += $ok ? 0 : 1;
    echo ($ok ? '  PASS ' : '  FAIL ') . $label . ($ok || $detail === '' ? '' : " ($detail)") . PHP_EOL;
}

$pdo = Database::pdo();
$scalar = function (string $sql, array $params = []) use ($pdo): mixed {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
};
$pdo->exec("delete from users where username like 'sectest_%'");
$pdo->exec('delete from rate_hits');

$suffix = bin2hex(random_bytes(3));
$password = 'Sec-' . bin2hex(random_bytes(12));
$register = function (Client $c, string $name) use ($password, $suffix): array {
    return $c->post('/register', [
        'username' => "sectest_{$name}_$suffix", 'email' => "sectest_{$name}_$suffix@example.test",
        'password' => $password, 'password_confirmation' => $password, 'consent' => '1',
    ], '/register');
};

echo "Headers and cookies\n";
$home = (new Client($base))->request('GET', '/login');
check('CSP header present', (bool) preg_match("/Content-Security-Policy: default-src 'none'; script-src 'self'/i", $home['headers']));
check('nosniff + frame-ancestors', str_contains($home['headers'], 'nosniff') && str_contains($home['headers'], "frame-ancestors 'none'"));
check('session cookie is HttpOnly; Secure; SameSite=Strict', (bool) preg_match('/Set-Cookie: __Host-rrsid=[a-f0-9]{64}; path=\/; secure; HttpOnly; SameSite=Strict/i', $home['headers']), trim(preg_replace('/.*(Set-Cookie:[^\r\n]*).*/s', '$1', $home['headers'])));

echo "Registration and IDOR\n";
$alice = new Client($base);
$bob = new Client($base);
check('alice registers', $register($alice, 'alice')['status'] === 303);
check('bob registers', $register($bob, 'bob')['status'] === 303);
$alice->post('/assets', ['hostname' => 'alice-secret.example.net', 'environment' => 'production', 'status' => 'online'], '/assets/new');
preg_match('#/assets/(\d+)/edit#', $alice->request('GET', '/assets')['body'], $m);
$aliceAsset = (int) ($m[1] ?? 0);
check('alice created an asset', $aliceAsset > 0);

check('bob cannot view alice\'s edit page', $bob->request('GET', "/assets/$aliceAsset/edit")['status'] === 404);
$put = $bob->post("/assets/$aliceAsset", ['_method' => 'PUT', 'hostname' => 'pwned.example.net', 'environment' => 'production', 'status' => 'offline'], '/assets/new');
check('bob cannot update alice\'s asset', $put['status'] === 404);
$del = $bob->post("/assets/$aliceAsset", ['_method' => 'DELETE'], '/assets/new');
check('bob cannot delete alice\'s asset', $del['status'] === 404);
check('bob\'s list does not include alice\'s asset', !str_contains($bob->request('GET', '/assets')['body'], 'alice-secret'));
check('alice\'s asset is unchanged', str_contains($alice->request('GET', '/assets')['body'], 'alice-secret.example.net'));

$bobId = (int) $scalar('select id from users where username = ?', ["sectest_bob_$suffix"]);
Database::actAs($bobId);
$visible = $pdo->prepare('select count(*) from assets where id = ?');
$visible->execute([$aliceAsset]);
check('row-level security hides alice\'s row from bob at the database', (int) $visible->fetchColumn() === 0);
Database::actAs(null);
$visible->execute([$aliceAsset]);
check('row-level security hides all rows when no user is set', (int) $visible->fetchColumn() === 0);

echo "CSRF\n";
$noToken = $alice->request('POST', '/assets', ['hostname' => 'csrf.example.net', 'environment' => 'production', 'status' => 'online']);
check('POST without token is rejected', $noToken['status'] === 419);
$badToken = $alice->request('POST', '/assets', ['_token' => str_repeat('a', 64), 'hostname' => 'csrf.example.net', 'environment' => 'production', 'status' => 'online']);
check('POST with wrong token is rejected', $badToken['status'] === 419);
$crossOrigin = $alice->post('/assets', ['hostname' => 'csrf.example.net', 'environment' => 'production', 'status' => 'online'], '/assets/new', ['Origin: https://evil.example']);
check('cross-origin POST is rejected', $crossOrigin['status'] === 403);

echo "Input handling\n";
$sqli = $alice->request('GET', '/assets?q=' . rawurlencode("' or 1=1; drop table users; --") . '&sort=' . rawurlencode('hostname; drop table assets'));
check('SQL injection in search is treated as text', $sqli['status'] === 200 && str_contains($sqli['body'], 'No servers match'));
$invalid = $alice->post('/assets', ['hostname' => '<b>bad host</b>', 'ip_address' => '999.1.1.1', 'environment' => 'nope', 'status' => 'online'], '/assets/new');
check('invalid asset gets 422 with field errors', $invalid['status'] === 422 && substr_count($invalid['body'], 'field-error') === 3);
check('unknown route returns 404 page', $alice->request('GET', '/nope')['status'] === 404);
check('wrong method returns 405', $alice->request('GET', '/logout')['status'] === 405);

echo "Session expiry\n";
$sid = $alice->cookies['__Host-rrsid'];
$pdo->prepare("update sessions set data = regexp_replace(data, '_last_seen\\|i:\\d+;', '_last_seen|i:1;') where id = ?")->execute([$sid]);
$expired = $alice->request('GET', '/assets');
check('idle session redirects to login', $expired['status'] === 303 && str_contains($expired['headers'], 'Location: /login'));
check('expiry message is shown', str_contains($alice->request('GET', '/login')['body'], 'expired after 15 minutes'));

echo "Brute force\n";
$mallory = new Client($base);
$identifier = "sectest_bob_$suffix";
for ($i = 1; $i <= 5; $i++) {
    $mallory->post('/login', ['identifier' => $identifier, 'password' => "wrong-password-$i"]);
}
$locked = $mallory->post('/login', ['identifier' => $identifier, 'password' => $password]);
check('account locks after 5 failures even with the right password', $locked['status'] === 401);
check('lock is stored on the account', (bool) $scalar('select locked_until > now() from users where username = ?', [$identifier]));
$scalar('update users set locked_until = null where username = ? returning id', [$identifier]);
check('login works once unlocked', $mallory->post('/login', ['identifier' => $identifier, 'password' => $password])['status'] === 303);

$pdo->exec('delete from rate_hits');
$attacker = new Client($base);
$statuses = [];
for ($i = 0; $i < 21; $i++) {
    $statuses[] = $attacker->post('/login', ['identifier' => "nobody-$i", 'password' => 'x'])['status'];
}
check('IP rate limit returns 429 after 20 login attempts', end($statuses) === 429 && count(array_filter($statuses, fn ($s) => $s === 401)) === 20);

$pdo->exec("delete from users where username like 'sectest_%'");
$pdo->exec('delete from rate_hits');

echo $failures === 0 ? "\nAll checks passed.\n" : "\n$failures check(s) failed.\n";
exit($failures === 0 ? 0 : 1);
