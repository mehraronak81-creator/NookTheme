<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Location;
use Pterodactyl\Models\Database;
use Pterodactyl\Models\Allocation;
use Pterodactyl\Models\ActivityLog;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Helpers\SoftwareVersionService;
use Pterodactyl\Repositories\Wings\DaemonConfigurationRepository;

class BaseController extends Controller
{
    /**
     * BaseController constructor.
     */
    public function __construct(
        private SoftwareVersionService $version,
        private DaemonConfigurationRepository $daemonRepository
    ) {
    }

    /**
     * Return the admin index view with real system stats.
     */
    public function index(): View
    {
        $servers = Server::count();
        $nodes = Node::count();
        $users = User::count();
        $eggs = Egg::count();
        $suspendedServers = Server::where('status', Server::STATUS_SUSPENDED)->count();
        $activeServers = Server::whereNull('status')->count();
        $installingServers = Server::where('status', Server::STATUS_INSTALLING)->count();
        $failedServers = Server::whereIn('status', [Server::STATUS_INSTALL_FAILED, Server::STATUS_REINSTALL_FAILED])->count();
        $locations = Location::count();
        $databases = Database::count();
        $allocations = Allocation::count();
        $allocationsUsed = Allocation::whereNotNull('server_id')->count();

        // Node resource totals
        $totalMemory = Node::sum('memory');
        $totalDisk = Node::sum('disk');
        $allocatedMemory = Server::sum('memory');
        $allocatedDisk = Server::sum('disk');
        $nodesInMaintenance = Node::where('maintenance_mode', true)->count();

        // Recent activity
        $recentActivity = ActivityLog::with('actor')
            ->orderBy('timestamp', 'desc')
            ->limit(15)
            ->get();

        // Per-node stats for health monitor
        $nodeStats = Node::withCount('servers')
            ->with('location')
            ->get()
            ->map(function ($node) {
                $memUsed = (int) Server::where('node_id', $node->id)->sum('memory');
                $diskUsed = (int) Server::where('node_id', $node->id)->sum('disk');
                $memMax = $node->memory * (1 + ($node->memory_overallocate / 100));
                $diskMax = $node->disk * (1 + ($node->disk_overallocate / 100));
                return [
                    'id' => $node->id,
                    'name' => $node->name,
                    'fqdn' => $node->fqdn,
                    'scheme' => $node->scheme,
                    'maintenance_mode' => $node->maintenance_mode,
                    'memory' => $node->memory,
                    'memory_used' => $memUsed,
                    'memory_percent' => $memMax > 0 ? round(($memUsed / $memMax) * 100, 1) : 0,
                    'disk' => $node->disk,
                    'disk_used' => $diskUsed,
                    'disk_percent' => $diskMax > 0 ? round(($diskUsed / $diskMax) * 100, 1) : 0,
                    'servers_count' => $node->servers_count,
                    'location' => $node->location->short ?? 'Unknown',
                ];
            });

        return view('admin.index', [
            'version' => $this->version,
            'servers' => $servers,
            'nodes' => $nodes,
            'users' => $users,
            'eggs' => $eggs,
            'suspendedServers' => $suspendedServers,
            'activeServers' => $activeServers,
            'installingServers' => $installingServers,
            'failedServers' => $failedServers,
            'locations' => $locations,
            'databases' => $databases,
            'allocations' => $allocations,
            'allocationsUsed' => $allocationsUsed,
            'totalMemory' => $totalMemory,
            'totalDisk' => $totalDisk,
            'allocatedMemory' => $allocatedMemory,
            'allocatedDisk' => $allocatedDisk,
            'nodesInMaintenance' => $nodesInMaintenance,
            'recentActivity' => $recentActivity,
            'nodeStats' => $nodeStats,
        ]);
    }

    /**
     * Returns real-time system health data as JSON for AJAX polling.
     */
    public function systemHealth(): JsonResponse
    {
        $servers = Server::count();
        $nodes = Node::count();
        $users = User::count();
        $suspendedServers = Server::where('status', Server::STATUS_SUSPENDED)->count();
        $activeServers = Server::whereNull('status')->count();
        $installingServers = Server::where('status', Server::STATUS_INSTALLING)->count();
        $failedServers = Server::whereIn('status', [Server::STATUS_INSTALL_FAILED, Server::STATUS_REINSTALL_FAILED])->count();

        $totalMemory = Node::sum('memory');
        $totalDisk = Node::sum('disk');
        $allocatedMemory = Server::sum('memory');
        $allocatedDisk = Server::sum('disk');
        $nodesInMaintenance = Node::where('maintenance_mode', true)->count();
        $allocations = Allocation::count();
        $allocationsUsed = Allocation::whereNotNull('server_id')->count();

        // Per-node live data
        $nodeDetails = Node::withCount('servers')->get()->map(function ($node) {
            $memUsed = Server::where('node_id', $node->id)->sum('memory');
            $diskUsed = Server::where('node_id', $node->id)->sum('disk');
            $memMax = $node->memory * (1 + ($node->memory_overallocate / 100));
            $diskMax = $node->disk * (1 + ($node->disk_overallocate / 100));

            // Try to get live daemon status
            $online = true;
            try {
                $this->daemonRepository->setNode($node)->getSystemInformation();
            } catch (\Exception $e) {
                $online = false;
            }

            return [
                'id' => $node->id,
                'name' => $node->name,
                'fqdn' => $node->fqdn,
                'online' => $online,
                'maintenance_mode' => $node->maintenance_mode,
                'memory' => $node->memory,
                'memory_used' => (int) $memUsed,
                'memory_percent' => $memMax > 0 ? round(($memUsed / $memMax) * 100, 1) : 0,
                'disk' => $node->disk,
                'disk_used' => (int) $diskUsed,
                'disk_percent' => $diskMax > 0 ? round(($diskUsed / $diskMax) * 100, 1) : 0,
                'servers_count' => $node->servers_count,
            ];
        });

        return new JsonResponse([
            'servers' => [
                'total' => $servers,
                'active' => $activeServers,
                'suspended' => $suspendedServers,
                'installing' => $installingServers,
                'failed' => $failedServers,
            ],
            'nodes' => [
                'total' => $nodes,
                'maintenance' => $nodesInMaintenance,
                'online' => $nodeDetails->where('online', true)->count(),
                'offline' => $nodeDetails->where('online', false)->count(),
            ],
            'users' => $users,
            'resources' => [
                'memory_total' => $totalMemory,
                'memory_used' => (int) $allocatedMemory,
                'memory_percent' => $totalMemory > 0 ? round(($allocatedMemory / $totalMemory) * 100, 1) : 0,
                'disk_total' => $totalDisk,
                'disk_used' => (int) $allocatedDisk,
                'disk_percent' => $totalDisk > 0 ? round(($allocatedDisk / $totalDisk) * 100, 1) : 0,
            ],
            'allocations' => [
                'total' => $allocations,
                'used' => $allocationsUsed,
                'free' => $allocations - $allocationsUsed,
            ],
            'node_details' => $nodeDetails,
        ]);
    }
}
