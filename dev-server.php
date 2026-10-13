<?php

// Router for `php -S localhost:8091 dev-server.php`: serves public/ files, sends everything else to the app.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = realpath(__DIR__ . '/public' . $path);
if ($path !== '/' && $file !== false && is_file($file) && str_starts_with($file, realpath(__DIR__ . '/public'))) {
    return false;
}

require __DIR__ . '/api/index.php';
