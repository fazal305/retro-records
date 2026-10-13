<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Middleware;
use App\Http\Request;
use App\Http\Response;
use App\Security\RateLimiter;

final class Throttle implements Middleware
{
    public function __construct(
        private readonly string $bucket,
        private readonly int $max,
        private readonly int $windowSeconds,
        private readonly bool $mutatingOnly = true,
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        if ($this->mutatingOnly && !$request->isMutating()) {
            return $next($request);
        }

        if (!RateLimiter::attempt($this->bucket, $request->ip(), $this->max, $this->windowSeconds)) {
            $minutes = (int) ceil($this->windowSeconds / 60);
            return Response::error(
                429,
                "Too many requests from your network. Wait about $minutes minutes and try again.",
                ['Retry-After' => (string) $this->windowSeconds],
            );
        }

        return $next($request);
    }
}
