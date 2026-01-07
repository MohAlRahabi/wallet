<?php

namespace App\Http\Middleware;

use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    use ResponseTrait;

    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $next($request);
        }

        $idempotencyKey = $request->header('Idempotency-Key');
        if (!$idempotencyKey) {
            return $this->errorResponse(message: "Missing idempotency key");
        }

        if (!$this->isValidKey($idempotencyKey)) {
            return $this->errorResponse(message: "Invalid Idempotency-Key format.");
        }

        $cacheKey = $this->buildCacheKey($request, $idempotencyKey);

        if (cache()->has($cacheKey)) {
            $cachedResponse = cache()->get($cacheKey);

            return $this->response($cachedResponse['body']['data'], $cachedResponse['body']['code'], $cachedResponse['body']['message'])
                ->withHeaders($cachedResponse['headers'])
                ->header('X-Idempotent-Replayed', 'true');
        }
        $lockKey = "{$cacheKey}:lock";
        if (cache()->has($lockKey)) {
            return $this->errorResponse(statusCode: 409, message: "Request is currently being processed");
        }

        cache()->put($lockKey, true, now()->addMinutes(5));


        try {
            $response = $next($request);

            if ($response->status() >= 200 && $response->status() < 300) {
                $this->cacheResponse($cacheKey, $response);
            }

            return $response;

        } finally {
            cache()->forget($lockKey);
        }
    }

    private function buildCacheKey(Request $request, string $idempotencyKey): string
    {
        $userId = $request->user()?->id ?? 'guest';
        $path = $request->path();
        $method = $request->method();

        return "idempotency:{$userId}:{$method}:{$path}:{$idempotencyKey}";
    }

    private function isValidKey(string $key): bool
    {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $key);
    }

    private function cacheResponse(string $cacheKey, Response $response): void
    {
        $cachedData = [
            'status' => $response->getStatusCode(),
            'headers' => $response->headers->all(),
            'body' => json_decode($response->getContent(), true),
        ];

        cache()->put($cacheKey, $cachedData, now()->addHours(24));
    }
}
