<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;

class SetSecurityHeaders
{
    /**
     * Ideally we move away from X-Frame-Options/X-XSS-Protection and implement a
     * proper standard CSP, but I can guarantee that will break for a lot of folks
     * using custom plugins and who knows what image embeds.
     *
     * We'll circle back to that at a later date when it can be more fully controlled
     * by the admin to support those cases without too much trouble.
     */
    private static array $headers = [
        // Prevent clickjacking
        'X-Frame-Options' => 'DENY',
        // Prevent MIME type sniffing
        'X-Content-Type-Options' => 'nosniff',
        // XSS Protection
        'X-XSS-Protection' => '1; mode=block',
        // Control referrer information
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        // Content Security Policy - blocks miners, inline scripts injection
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' wss: ws:; frame-src https://www.google.com https://www.recaptcha.net; worker-src 'none'; child-src 'none'",
        // Prevent DNS prefetching abuse
        'X-DNS-Prefetch-Control' => 'off',
        // Only allow HTTPS
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
        // Permissions Policy - block dangerous browser APIs used by miners
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()',
        // Prevent page from being cached with sensitive data
        'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
        'Pragma' => 'no-cache',
        // Cross-Origin policies
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Cross-Origin-Resource-Policy' => 'same-origin',
    ];

    /**
     * Enforces some basic security headers on all responses returned by the software.
     * If a header has already been set in another location within the code it will be
     * skipped over here.
     *
     * @param (\Closure(mixed): \Illuminate\Http\Response) $next
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $response = $next($request);

        foreach (static::$headers as $key => $value) {
            if (! $response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }
}
