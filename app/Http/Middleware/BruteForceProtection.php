<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class BruteForceProtection
{
    /**
     * Protect login/auth routes from brute force attacks.
     * Locks out after 5 failed attempts for 15 minutes, escalating.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $ip = $request->ip();
        $key = 'brute_force:' . $ip;
        $lockoutKey = 'brute_lockout:' . $ip;

        // Check if currently locked out
        if (Cache::has($lockoutKey)) {
            $lockoutData = Cache::get($lockoutKey);
            $remaining = max(0, $lockoutData['until'] - time());

            throw new TooManyRequestsHttpException(
                $remaining,
                'Too many failed attempts. Please try again in ' . ceil($remaining / 60) . ' minute(s).'
            );
        }

        $response = $next($request);

        // If it's a POST to auth routes and response is a redirect (failed login)
        if ($request->isMethod('POST') && $response->getStatusCode() >= 400) {
            $attempts = Cache::get($key, 0) + 1;
            Cache::put($key, $attempts, now()->addHour());

            // Escalating lockout: 5 fails = 15min, 10 = 30min, 15 = 1hr, 20+ = auto-ban
            if ($attempts >= 20) {
                // Auto-ban the IP
                $autoBlocked = Cache::get('admin_auto_blocked_ips', []);
                $autoBlocked[$ip] = [
                    'ip' => $ip,
                    'reason' => 'Auto-blocked: 20+ failed login attempts',
                    'blocked_at' => now()->toDateTimeString(),
                    'violations' => $attempts,
                ];
                Cache::put('admin_auto_blocked_ips', $autoBlocked, now()->addYear());
            } elseif ($attempts >= 15) {
                Cache::put($lockoutKey, ['until' => time() + 3600, 'attempts' => $attempts], now()->addHour());
            } elseif ($attempts >= 10) {
                Cache::put($lockoutKey, ['until' => time() + 1800, 'attempts' => $attempts], now()->addMinutes(30));
            } elseif ($attempts >= 5) {
                Cache::put($lockoutKey, ['until' => time() + 900, 'attempts' => $attempts], now()->addMinutes(15));
            }
        } elseif ($request->isMethod('POST') && $response->getStatusCode() < 400) {
            // Successful auth, clear attempts
            Cache::forget($key);
            Cache::forget($lockoutKey);
        }

        return $response;
    }
}
