<?php

declare(strict_types=1);

namespace App;

use App\Security\Session;

final class View
{
    private const ROOT = __DIR__ . '/../templates/';

    private static array $shared = ['user' => null];

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function render(string $template, array $data = []): string
    {
        $data += self::$shared + ['title' => null, 'nav' => null, 'errors' => []];
        $content = self::capture($template, $data);
        $layout = $data['user'] !== null ? 'layouts/app' : 'layouts/public';

        return self::capture($layout, $data + ['content' => $content, 'flashes' => self::flashes()]);
    }

    public static function partial(string $template, array $data = []): string
    {
        return self::capture('partials/' . $template, $data);
    }

    private static function capture(string $template, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            require self::ROOT . $template . '.php';
            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    private static function flashes(): array
    {
        return session_status() === PHP_SESSION_ACTIVE ? Session::pullFlashes() : [];
    }
}
