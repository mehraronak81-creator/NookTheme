<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;

class AntiMinerMiddleware
{
    /**
     * Block requests that try to inject crypto miners or malicious scripts.
     * NOTE: This middleware should NOT be in the global middleware stack.
     * It is available as a route middleware alias 'anti.miner' for selective use.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        // Block requests with suspicious User-Agents (known miner patterns only)
        $userAgent = strtolower($request->userAgent() ?? '');
        $maliciousPatterns = [
            'coinhive', 'cryptoloot', 'minero', 'jsecoin', 'webminer',
            'deepminer', 'cryptonight', 'xmrig', 'nicehash',
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                abort(403, 'Forbidden');
            }
        }

        return $next($request);
    }
}
