<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class IpBanMiddleware
{
    /**
     * Check if the IP is banned (manual + auto-blocked).
     * Returns 403 if banned.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $ip = $request->ip();

        // Check manual bans
        $bannedIps = Cache::get('admin_banned_ips', []);
        if (isset($bannedIps[$ip])) {
            $ban = $bannedIps[$ip];

            // Check if ban has expired
            if (!empty($ban['expires_at']) && now()->gt($ban['expires_at'])) {
                // Ban expired, remove it
                unset($bannedIps[$ip]);
                Cache::put('admin_banned_ips', $bannedIps, now()->addYear());
            } else {
                throw new AccessDeniedHttpException(
                    'Your IP address has been banned. Reason: ' . ($ban['reason'] ?? 'No reason provided.')
                );
            }
        }

        // Check auto-blocked IPs
        $autoBlocked = Cache::get('admin_auto_blocked_ips', []);
        if (isset($autoBlocked[$ip])) {
            throw new AccessDeniedHttpException(
                'Your IP has been automatically blocked due to suspicious activity.'
            );
        }

        return $next($request);
    }
}
