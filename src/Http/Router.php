<?php

declare(strict_types=1);

namespace App\Http;

final class Router
{
    private array $routes = [];
    private array $global = [];

    public function use(Middleware ...$middleware): void
    {
        array_push($this->global, ...$middleware);
    }

    public function add(string $method, string $pattern, callable $handler, array $middleware = []): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', $pattern) . '$#';
        $this->routes[] = compact('method', 'regex', 'handler', 'middleware');
    }

    public function get(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add('POST', $pattern, $handler, $middleware);
    }

    public function put(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add('PUT', $pattern, $handler, $middleware);
    }

    public function delete(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add('DELETE', $pattern, $handler, $middleware);
    }

    public function dispatch(Request $request): Response
    {
        $route = null;
        $allowed = [];
        foreach ($this->routes as $candidate) {
            if (!preg_match($candidate['regex'], $request->path, $matches)) {
                continue;
            }
            $method = $request->method === 'HEAD' ? 'GET' : $request->method;
            if ($candidate['method'] !== $method) {
                $allowed[] = $candidate['method'];
                continue;
            }
            $route = $candidate;
            $request->params = array_map('intval', array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
            break;
        }

        $final = match (true) {
            $route !== null => static fn (Request $r): Response => ($route['handler'])($r),
            $allowed !== [] => static fn (): Response => Response::error(405, null, ['Allow' => implode(', ', array_unique($allowed))]),
            default => static fn (): Response => Response::error(404),
        };

        $stack = array_merge($this->global, $route['middleware'] ?? []);
        $pipeline = array_reduce(
            array_reverse($stack),
            static fn (callable $next, Middleware $mw): callable => static fn (Request $r): Response => $mw->handle($r, $next),
            $final,
        );

        return $pipeline($request);
    }
}
