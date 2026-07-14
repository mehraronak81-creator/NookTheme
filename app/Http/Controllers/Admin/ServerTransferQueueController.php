<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\ServerTransfer;

class ServerTransferQueueController extends Controller
{
    /**
     * Show the server transfer queue.
     */
    public function index(Request $request): View
    {
        // Pending transfers
        $pendingTransfers = ServerTransfer::with(['server', 'server.user', 'server.node', 'oldNode', 'newNode'])
            ->whereNull('successful')
            ->orderBy('created_at', 'desc')
            ->get();

        // Completed transfers (last 50)
        $completedTransfers = ServerTransfer::with(['server', 'server.user', 'oldNode', 'newNode'])
            ->whereNotNull('successful')
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        // Stats
        $totalTransfers = ServerTransfer::count();
        $successfulTransfers = ServerTransfer::where('successful', true)->count();
        $failedTransfers = ServerTransfer::where('successful', false)->count();
        $inProgressTransfers = ServerTransfer::whereNull('successful')->count();

        // Node capacity for transfer recommendations
        $nodeCapacity = Node::withCount('servers')
            ->get()
            ->map(function ($node) {
                $memUsed = Server::where('node_id', $node->id)->sum('memory');
                $diskUsed = Server::where('node_id', $node->id)->sum('disk');
                $memMax = $node->memory * (1 + ($node->memory_overallocate / 100));
                $diskMax = $node->disk * (1 + ($node->disk_overallocate / 100));
                return [
                    'id' => $node->id,
                    'name' => $node->name,
                    'fqdn' => $node->fqdn,
                    'location' => $node->location->short ?? 'Unknown',
                    'maintenance' => $node->maintenance_mode,
                    'servers' => $node->servers_count,
                    'memory_used' => $memUsed,
                    'memory_total' => $node->memory,
                    'memory_max' => $memMax,
                    'memory_percent' => $memMax > 0 ? round(($memUsed / $memMax) * 100, 1) : 0,
                    'memory_free' => max(0, $memMax - $memUsed),
                    'disk_used' => $diskUsed,
                    'disk_total' => $node->disk,
                    'disk_max' => $diskMax,
                    'disk_percent' => $diskMax > 0 ? round(($diskUsed / $diskMax) * 100, 1) : 0,
                    'disk_free' => max(0, $diskMax - $diskUsed),
                ];
            })
            ->sortBy('memory_percent');

        // Servers eligible for transfer (not already transferring, not suspended)
        $transferableServers = Server::with(['user', 'node', 'node.location'])
            ->whereNull('status')
            ->whereDoesntHave('transfer', function ($q) {
                $q->whereNull('successful');
            })
            ->orderBy('name')
            ->get();

        return view('admin.transfers.index', [
            'pendingTransfers' => $pendingTransfers,
            'completedTransfers' => $completedTransfers,
            'totalTransfers' => $totalTransfers,
            'successfulTransfers' => $successfulTransfers,
            'failedTransfers' => $failedTransfers,
            'inProgressTransfers' => $inProgressTransfers,
            'nodeCapacity' => $nodeCapacity,
            'transferableServers' => $transferableServers,
        ]);
    }
}
