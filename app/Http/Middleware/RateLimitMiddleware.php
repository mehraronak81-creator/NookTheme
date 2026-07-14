<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class RateLimitMiddleware
{
    /**
     * Rate limit requests per IP. Anti-DDoS layer.
     * Default: 60 requests per minute for admin, 120 for API.
     */
    public function handle(Request $request, \Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): mixed
    {
        $ip = $request->ip();
        $key = 'rate_limit:' . $ip . ':' . $request->path();

        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            // Log the blocked request
            Cache::increment('rate_limit_blocked:' . $ip);

            // Auto-block IP if it hits rate limit 10+ times
            $blockedCount = Cache::get('rate_limit_blocked:' . $ip, 0);
            if ($blockedCount >= 10) {
                $autoBlocked = Cache::get('admin_auto_blocked_ips', []);
                if (!isset($autoBlocked[$ip])) {
                    $autoBlocked[$ip] = [
                        'ip' => $ip,
                        'reason' => 'Auto-blocked: Excessive rate limit violations',
                        'blocked_at' => now()->toDateTimeString(),
                        'violations' => $blockedCount,
                    ];
                    Cache::put('admin_auto_blocked_ips', $autoBlocked, now()->addYear());
                }
            }

            throw new TooManyRequestsHttpException(
                $decayMinutes * 60,
                'Too many requests. Please slow down.'
            );
        }

        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));

        $response = $next($request);

        // Add rate limit headers
        if (method_exists($response, 'headers')) {
            $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
            $response->headers->set('X-RateLimit-Remaining', (string) max(0, $maxAttempts - $attempts - 1));
        }

        return $response;
    }
}
