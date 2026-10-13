<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Middleware;
use App\Http\Request;
use App\Http\Response;
use PDOException;
use Throwable;

final class HandleErrors implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            error_log(sprintf('[%s] %s %s: %s in %s:%d', get_class($e), $request->method, $request->path, $e->getMessage(), $e->getFile(), $e->getLine()));

            if ($e instanceof PDOException && str_starts_with((string) $e->getCode(), '08')) {
                return Response::error(503, 'The records database is not reachable right now. Wait a minute and try again.', ['Retry-After' => '60']);
            }
            return Response::error(500);
        }
    }
}
