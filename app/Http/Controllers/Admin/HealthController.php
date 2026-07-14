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
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Repositories\Wings\DaemonConfigurationRepository;

class HealthController extends Controller
{
    public function __construct(
        private DaemonConfigurationRepository $daemonRepository
    ) {
    }

    /**
     * Show the system health monitor page.
     */
    public function index(): View
    {
        $nodes = Node::with('location')->withCount('servers')->get();

        $nodeData = $nodes->map(function ($node) {
            $memUsed = Server::where('node_id', $node->id)->sum('memory');
            $diskUsed = Server::where('node_id', $node->id)->sum('disk');
            $memMax = $node->memory * (1 + ($node->memory_overallocate / 100));
            $diskMax = $node->disk * (1 + ($node->disk_overallocate / 100));
            $suspended = Server::where('node_id', $node->id)->where('status', Server::STATUS_SUSPENDED)->count();
            $active = Server::where('node_id', $node->id)->whereNull('status')->count();

            return [
                'id' => $node->id,
                'name' => $node->name,
                'fqdn' => $node->fqdn,
                'scheme' => $node->scheme,
                'maintenance_mode' => $node->maintenance_mode,
                'location' => $node->location->short ?? 'Unknown',
                'memory_total' => $node->memory,
                'memory_max' => (int) $memMax,
                'memory_used' => (int) $memUsed,
                'memory_percent' => $memMax > 0 ? round(($memUsed / $memMax) * 100, 1) : 0,
                'disk_total' => $node->disk,
                'disk_max' => (int) $diskMax,
                'disk_used' => (int) $diskUsed,
                'disk_percent' => $diskMax > 0 ? round(($diskUsed / $diskMax) * 100, 1) : 0,
                'servers_total' => $node->servers_count,
                'servers_active' => $active,
                'servers_suspended' => $suspended,
                'memory_overallocate' => $node->memory_overallocate,
                'disk_overallocate' => $node->disk_overallocate,
            ];
        });

        $totalMemory = $nodes->sum('memory');
        $totalDisk = $nodes->sum('disk');
        $allocatedMemory = Server::sum('memory');
        $allocatedDisk = Server::sum('disk');

        return view('admin.health.index', [
            'nodeData' => $nodeData,
            'totalMemory' => $totalMemory,
            'totalDisk' => $totalDisk,
            'allocatedMemory' => (int) $allocatedMemory,
            'allocatedDisk' => (int) $allocatedDisk,
            'totalServers' => Server::count(),
            'activeServers' => Server::whereNull('status')->count(),
            'suspendedServers' => Server::where('status', Server::STATUS_SUSPENDED)->count(),
            'installingServers' => Server::where('status', Server::STATUS_INSTALLING)->count(),
            'failedServers' => Server::whereIn('status', [Server::STATUS_INSTALL_FAILED, Server::STATUS_REINSTALL_FAILED])->count(),
            'totalNodes' => $nodes->count(),
            'nodesInMaintenance' => $nodes->where('maintenance_mode', true)->count(),
            'totalUsers' => User::count(),
            'totalAllocations' => Allocation::count(),
            'usedAllocations' => Allocation::whereNotNull('server_id')->count(),
            'totalDatabases' => Database::count(),
            'totalEggs' => Egg::count(),
            'totalLocations' => Location::count(),
        ]);
    }

    /**
     * Check a specific node's daemon connectivity.
     */
    public function checkNode(Node $node): JsonResponse
    {
        try {
            $data = $this->daemonRepository->setNode($node)->getSystemInformation();
            return new JsonResponse([
                'online' => true,
                'version' => $data['version'] ?? 'unknown',
                'os' => $data['os'] ?? 'unknown',
                'architecture' => $data['architecture'] ?? 'unknown',
                'kernel' => $data['kernel_version'] ?? 'unknown',
                'cpu_count' => $data['cpu_count'] ?? 0,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'online' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
