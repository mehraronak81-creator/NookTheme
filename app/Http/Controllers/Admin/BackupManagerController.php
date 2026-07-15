<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Backup;
use Pterodactyl\Models\Node;
use Pterodactyl\Http\Controllers\Controller;

class BackupManagerController extends Controller
{
    /**
     * Show global backup manager.
     */
    public function index(Request $request): View
    {
        $query = Backup::with(['server', 'server.user', 'server.node']);

        if ($request->filled('server')) {
            $query->where('server_id', $request->input('server'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'completed') {
                $query->where('is_successful', true)->whereNotNull('completed_at');
            } elseif ($request->input('status') === 'failed') {
                $query->where('is_successful', false)->whereNotNull('completed_at');
            } elseif ($request->input('status') === 'pending') {
                $query->whereNull('completed_at');
            }
        }

        $backups = $query->orderBy('created_at', 'desc')->paginate(50);

        $totalBackups = Backup::count();
        $completedBackups = Backup::where('is_successful', true)->count();
        $failedBackups = Backup::where('is_successful', false)->whereNotNull('completed_at')->count();
        $pendingBackups = Backup::whereNull('completed_at')->count();
        $totalSize = Backup::where('is_successful', true)->sum('bytes');

        // Per-node backup stats
        $nodeBackupStats = Node::with(['servers.backups'])->get()->map(function ($node) {
            $backupCount = 0;
            $backupSize = 0;
            foreach ($node->servers as $server) {
                $backupCount += $server->backups->count();
                $backupSize += $server->backups->where('is_successful', true)->sum('bytes');
            }
            return [
                'name' => $node->name,
                'backup_count' => $backupCount,
                'backup_size' => $backupSize,
            ];
        });

        return view('admin.backups.index', [
            'backups' => $backups,
            'totalBackups' => $totalBackups,
            'completedBackups' => $completedBackups,
            'failedBackups' => $failedBackups,
            'pendingBackups' => $pendingBackups,
            'totalSize' => $totalSize,
            'nodeBackupStats' => $nodeBackupStats,
            'currentServer' => $request->input('server', ''),
            'currentStatus' => $request->input('status', ''),
        ]);
    }
}
