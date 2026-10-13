<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(App\Security\Csrf::token()) . '">';
}

function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . e($method) . '">';
}

function url(string $path, array $query = []): string
{
    $query = array_filter($query, static fn ($v) => $v !== null && $v !== '');
    return $path . ($query ? '?' . http_build_query($query) : '');
}

function fmt_date(?string $timestamp): string
{
    if ($timestamp === null) {
        return '—';
    }
    return (new DateTimeImmutable($timestamp))->setTimezone(new DateTimeZone('UTC'))->format('j M Y, H:i') . ' UTC';
}

function asset(string $path): string
{
    return '/' . ltrim($path, '/') . '?v=' . App\Config::get('ASSET_VERSION', '1');
}
