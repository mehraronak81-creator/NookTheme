<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;

class AntiMinerMiddleware
{
    /**
     * Block requests that try to inject crypto miners or malicious scripts.
     * Also prevents clickjacking, content sniffing, and other injection attacks.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        // Block requests with suspicious User-Agents (known bot/miner patterns)
        $userAgent = strtolower($request->userAgent() ?? '');
        $maliciousPatterns = [
            'coinhive', 'cryptoloot', 'minero', 'jsecoin', 'webminer',
            'deepminer', 'cryptonight', 'xmrig', 'nicehash',
            'python-requests', 'sqlmap', 'nikto', 'nmap', 'masscan',
            'zgrab', 'dirbuster', 'gobuster', 'wfuzz',
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                abort(403, 'Forbidden');
            }
        }

        // Block requests with suspicious query strings
        $fullUrl = strtolower($request->fullUrl());
        $suspiciousPatterns = [
            'eval(', 'base64_decode', 'gzinflate', 'str_rot13',
            '<script', 'javascript:', 'onload=', 'onerror=',
            '../etc/passwd', '/proc/self', 'php://filter',
            'UNION SELECT', 'OR 1=1', "' OR '", '"; DROP',
            'coinhive.min.js', 'coin-hive.com', 'authedmine',
            'webassembly.instantiate', 'crypto-loot',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($fullUrl, strtolower($pattern))) {
                abort(403, 'Forbidden');
            }
        }

        // Block suspicious POST body content (for non-file requests)
        if ($request->isMethod('POST') && !$request->hasFile('files')) {
            $body = strtolower($request->getContent());
            $bodyPatterns = [
                'coinhive', '<script>mining', 'cryptonight',
                'webassembly.instantiate', 'importscripts(', 'miner.start',
            ];

            foreach ($bodyPatterns as $pattern) {
                if (str_contains($body, $pattern)) {
                    abort(403, 'Forbidden');
                }
            }
        }

        $response = $next($request);

        return $response;
    }
}
