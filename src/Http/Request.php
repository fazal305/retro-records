<?php

declare(strict_types=1);

namespace App\Http;

use App\Config;

final class Request
{
    private const OVERRIDABLE = ['PUT', 'PATCH', 'DELETE'];

    public array $params = [];
    public ?array $user = null;

    public function __construct(
        public readonly string $method,
        public readonly string $path,
        private readonly array $query,
        private readonly array $body,
        private readonly array $server,
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $override = strtoupper((string) ($_POST['_method'] ?? ''));
        if ($method === 'POST' && in_array($override, self::OVERRIDABLE, true)) {
            $method = $override;
        }

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $path = '/' . trim(rawurldecode($path), '/');

        return new self($method, $path, $_GET, $_POST, $_SERVER);
    }

    public function input(string $key, string $default = ''): string
    {
        $value = $this->body[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    public function raw(string $key): string
    {
        $value = $this->body[$key] ?? '';
        return is_string($value) ? $value : '';
    }

    public function query(string $key, string $default = ''): string
    {
        $value = $this->query[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return isset($this->server[$key]) ? (string) $this->server[$key] : null;
    }

    public function isMutating(): bool
    {
        return !in_array($this->method, ['GET', 'HEAD', 'OPTIONS'], true);
    }

    public function ip(): string
    {
        if (Config::get('TRUST_PROXY') === 'vercel') {
            $forwarded = $this->header('X-Vercel-Forwarded-For') ?? $this->header('X-Forwarded-For') ?? '';
            $first = trim(explode(',', $forwarded)[0]);
            if (filter_var($first, FILTER_VALIDATE_IP)) {
                return $first;
            }
        }
        return (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function host(): string
    {
        return (string) ($this->server['HTTP_HOST'] ?? '');
    }

    public function fullUrl(): string
    {
        return $this->path . (empty($this->query) ? '' : '?' . http_build_query($this->query));
    }
}
