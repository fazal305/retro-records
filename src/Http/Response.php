<?php

declare(strict_types=1);

namespace App\Http;

use App\View;

final class Response
{
    public function __construct(
        public string $body = '',
        public int $status = 200,
        public array $headers = [],
    ) {
    }

    public static function view(string $template, array $data = [], int $status = 200): self
    {
        return new self(View::render($template, $data), $status, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public static function redirect(string $to, int $status = 303): self
    {
        if (!str_starts_with($to, '/') || preg_match('#^/[/\\\\]#', $to)) {
            $to = '/';
        }
        return new self('', $status, ['Location' => $to]);
    }

    public static function error(int $status, ?string $message = null, array $headers = []): self
    {
        $response = self::view('error', ['status' => $status, 'message' => $message, 'title' => "Error $status"], $status);
        $response->headers += $headers;
        return $response;
    }

    public static function json(array $data, int $status = 200): self
    {
        return new self(json_encode($data, JSON_THROW_ON_ERROR), $status, ['Content-Type' => 'application/json']);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->body;
    }
}
