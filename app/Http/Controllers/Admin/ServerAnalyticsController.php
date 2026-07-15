<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Allocation;
use Pterodactyl\Models\Database;
use Pterodactyl\Models\ActivityLog;
use Pterodactyl\Http\Controllers\Controller;

class ServerAnalyticsController extends Controller
{
    /**
     * Server analytics dashboard with charts and breakdowns.
     */
    public function index(): View
    {
        // Per-node server distribution
        $nodeDistribution = Node::withCount('servers')->orderBy('servers_count', 'desc')->get();

        // Server status breakdown
        $statusBreakdown = [
            'active' => Server::whereNull('status')->count(),
            'suspended' => Server::where('status', Server::STATUS_SUSPENDED)->count(),
            'installing' => Server::where('status', Server::STATUS_INSTALLING)->count(),
            'failed' => Server::whereIn('status', [Server::STATUS_INSTALL_FAILED, Server::STATUS_REINSTALL_FAILED])->count(),
        ];

        // Top resource consumers
        $topMemoryServers = Server::with(['node', 'user'])->orderBy('memory', 'desc')->limit(10)->get();
        $topDiskServers = Server::with(['node', 'user'])->orderBy('disk', 'desc')->limit(10)->get();

        // Per-user server counts
        $userServerCounts = User::withCount('servers')
            ->orderBy('servers_count', 'desc')
            ->limit(20)
            ->get()
            ->filter(fn($u) => $u->servers_count > 0)
            ->values();

        // Resource allocation summary
        $totalAllocatedMem = Server::sum('memory');
        $totalAllocatedDisk = Server::sum('disk');
        $totalAvailMem = Node::sum('memory');
        $totalAvailDisk = Node::sum('disk');

        // Database count per server
        $serversWithDb = Server::has('databases')->withCount('databases')
            ->orderBy('databases_count', 'desc')
            ->limit(10)
            ->get();

        // Egg popularity
        $eggPopularity = Server::with('egg')
            ->selectRaw('egg_id, COUNT(*) as count')
            ->groupBy('egg_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.analytics.index', [
            'nodeDistribution' => $nodeDistribution,
            'statusBreakdown' => $statusBreakdown,
            'topMemoryServers' => $topMemoryServers,
            'topDiskServers' => $topDiskServers,
            'userServerCounts' => $userServerCounts,
            'totalAllocatedMem' => $totalAllocatedMem,
            'totalAllocatedDisk' => $totalAllocatedDisk,
            'totalAvailMem' => $totalAvailMem,
            'totalAvailDisk' => $totalAvailDisk,
            'serversWithDb' => $serversWithDb,
            'eggPopularity' => $eggPopularity,
        ]);
    }

    /**
     * JSON data for charts.
     */
    public function chartData(): JsonResponse
    {
        $nodes = Node::withCount('servers')->get();
        $labels = $nodes->pluck('name');
        $data = $nodes->pluck('servers_count');

        $memPerNode = $nodes->map(fn($n) => [
            'name' => $n->name,
            'total' => $n->memory,
            'used' => Server::where('node_id', $n->id)->sum('memory'),
        ]);

        return new JsonResponse([
            'server_distribution' => ['labels' => $labels, 'data' => $data],
            'memory_per_node' => $memPerNode,
            'status_breakdown' => [
                'active' => Server::whereNull('status')->count(),
                'suspended' => Server::where('status', Server::STATUS_SUSPENDED)->count(),
                'installing' => Server::where('status', Server::STATUS_INSTALLING)->count(),
                'failed' => Server::whereIn('status', [Server::STATUS_INSTALL_FAILED, Server::STATUS_REINSTALL_FAILED])->count(),
            ],
        ]);
    }
}
