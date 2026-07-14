<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Pterodactyl\Models\Node;

class SystemEnvironmentController extends Controller
{
    /**
     * Show system environment diagnostics page.
     * This performs real checks against the server environment.
     */
    public function index(): View
    {
        // PHP Info
        $phpVersion = PHP_VERSION;
        $phpMinimum = '8.1.0';
        $phpOk = version_compare($phpVersion, $phpMinimum, '>=');

        // Required PHP extensions
        $requiredExtensions = [
            'bcmath', 'ctype', 'curl', 'dom', 'fileinfo',
            'gd', 'json', 'mbstring', 'openssl', 'pdo',
            'pdo_mysql', 'tokenizer', 'xml', 'zip',
        ];
        $extensionStatus = [];
        foreach ($requiredExtensions as $ext) {
            $extensionStatus[$ext] = extension_loaded($ext);
        }

        // Composer dependencies check
        $composerLock = base_path('composer.lock');
        $composerOk = file_exists($composerLock);

        // Key directories and their write permissions
        $directories = [
            'storage/app' => storage_path('app'),
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];
        $dirStatus = [];
        foreach ($directories as $label => $path) {
            $dirStatus[$label] = [
                'exists' => is_dir($path),
                'writable' => is_writable($path),
            ];
        }

        // Database connectivity
        $dbOk = false;
        $dbVersion = 'Unknown';
        $dbDriver = config('database.default');
        try {
            $dbVersion = DB::select('SELECT VERSION() as ver')[0]->ver ?? 'Unknown';
            $dbOk = true;
        } catch (\Exception $e) {
            $dbVersion = 'Connection failed: ' . $e->getMessage();
        }

        // Cache driver
        $cacheDriver = config('cache.default');
        $cacheOk = false;
        try {
            Cache::put('vh_env_check', 'ok', 10);
            $cacheOk = Cache::get('vh_env_check') === 'ok';
            Cache::forget('vh_env_check');
        } catch (\Exception $e) {
            $cacheOk = false;
        }

        // Queue driver
        $queueDriver = config('queue.default');

        // Session driver
        $sessionDriver = config('session.driver');

        // Mail driver
        $mailDriver = config('mail.default');

        // App environment
        $appEnv = config('app.env');
        $appDebug = config('app.debug');
        $appUrl = config('app.url');

        // Node connectivity summary
        $totalNodes = Node::count();
        $nodesOnline = 0;
        $nodesOffline = 0;
        $nodeChecks = [];

        $nodes = Node::all();
        foreach ($nodes as $node) {
            $status = 'unknown';
            $latency = null;
            try {
                $start = microtime(true);
                $ch = curl_init($node->scheme . '://' . $node->fqdn . ':' . $node->daemonListen . '/api/system');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 5,
                    CURLOPT_CONNECTTIMEOUT => 3,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $node->daemon_token,
                    ],
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $latency = round((microtime(true) - $start) * 1000);

                if ($httpCode >= 200 && $httpCode < 400) {
                    $status = 'online';
                    $nodesOnline++;
                } else {
                    $status = 'offline';
                    $nodesOffline++;
                }
            } catch (\Exception $e) {
                $status = 'offline';
                $nodesOffline++;
            }

            $nodeChecks[] = [
                'name' => $node->name,
                'fqdn' => $node->fqdn,
                'status' => $status,
                'latency' => $latency,
                'maintenance' => $node->maintenance_mode,
            ];
        }

        // Disk space
        $diskFree = disk_free_space(base_path());
        $diskTotal = disk_total_space(base_path());
        $diskPercent = $diskTotal > 0 ? round(($diskFree / $diskTotal) * 100, 1) : 0;

        // Memory limit
        $memoryLimit = ini_get('memory_limit');

        // Max execution time
        $maxExecTime = ini_get('max_execution_time');

        // Upload limits
        $uploadMax = ini_get('upload_max_filesize');
        $postMax = ini_get('post_max_size');

        return view('admin.environment.index', [
            'phpVersion' => $phpVersion,
            'phpMinimum' => $phpMinimum,
            'phpOk' => $phpOk,
            'extensionStatus' => $extensionStatus,
            'composerOk' => $composerOk,
            'dirStatus' => $dirStatus,
            'dbOk' => $dbOk,
            'dbVersion' => $dbVersion,
            'dbDriver' => $dbDriver,
            'cacheDriver' => $cacheDriver,
            'cacheOk' => $cacheOk,
            'queueDriver' => $queueDriver,
            'sessionDriver' => $sessionDriver,
            'mailDriver' => $mailDriver,
            'appEnv' => $appEnv,
            'appDebug' => $appDebug,
            'appUrl' => $appUrl,
            'totalNodes' => $totalNodes,
            'nodesOnline' => $nodesOnline,
            'nodesOffline' => $nodesOffline,
            'nodeChecks' => $nodeChecks,
            'diskFree' => $diskFree,
            'diskTotal' => $diskTotal,
            'diskPercent' => $diskPercent,
            'memoryLimit' => $memoryLimit,
            'maxExecTime' => $maxExecTime,
            'uploadMax' => $uploadMax,
            'postMax' => $postMax,
        ]);
    }
}
