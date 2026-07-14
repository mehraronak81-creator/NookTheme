<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Allocation;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Repositories\Wings\DaemonConfigurationRepository;

class ResourceMonitorController extends Controller
{
    public function __construct(
        private DaemonConfigurationRepository $daemonRepository
    ) {
    }

    /**
     * Resource monitoring dashboard with real-time data.
     */
    public function index(): View
    {
        $nodes = Node::with('location')->withCount('servers')->get();

        $nodeResources = $nodes->map(function ($node) {
            $memUsed = Server::where('node_id', $node->id)->sum('memory');
            $diskUsed = Server::where('node_id', $node->id)->sum('disk');
            $allocTotal = Allocation::where('node_id', $node->id)->count();
            $allocUsed = Allocation::where('node_id', $node->id)->whereNotNull('server_id')->count();

            $memMax = $node->memory * (1 + ($node->memory_overallocate / 100));
            $diskMax = $node->disk * (1 + ($node->disk_overallocate / 100));

            return [
                'id' => $node->id,
                'name' => $node->name,
                'fqdn' => $node->fqdn,
                'location' => $node->location->short ?? 'N/A',
                'maintenance' => $node->maintenance_mode,
                'servers_count' => $node->servers_count,
                'memory_total' => $node->memory,
                'memory_max' => (int) $memMax,
                'memory_used' => (int) $memUsed,
                'memory_percent' => $memMax > 0 ? round(($memUsed / $memMax) * 100, 1) : 0,
                'disk_total' => $node->disk,
                'disk_max' => (int) $diskMax,
                'disk_used' => (int) $diskUsed,
                'disk_percent' => $diskMax > 0 ? round(($diskUsed / $diskMax) * 100, 1) : 0,
                'alloc_total' => $allocTotal,
                'alloc_used' => $allocUsed,
                'alloc_percent' => $allocTotal > 0 ? round(($allocUsed / $allocTotal) * 100, 1) : 0,
                'overalloc_mem' => $node->memory_overallocate,
                'overalloc_disk' => $node->disk_overallocate,
            ];
        });

        // Alerts for overloaded nodes
        $alerts = $nodeResources->filter(fn($n) => $n['memory_percent'] > 85 || $n['disk_percent'] > 85);

        return view('admin.resources.index', [
            'nodeResources' => $nodeResources,
            'alerts' => $alerts,
            'totalMem' => $nodes->sum('memory'),
            'totalDisk' => $nodes->sum('disk'),
            'usedMem' => (int) Server::sum('memory'),
            'usedDisk' => (int) Server::sum('disk'),
        ]);
    }

    /**
     * Live node resource data as JSON.
     */
    public function liveData(): JsonResponse
    {
        $nodes = Node::withCount('servers')->get();

        $data = $nodes->map(function ($node) {
            $memUsed = Server::where('node_id', $node->id)->sum('memory');
            $diskUsed = Server::where('node_id', $node->id)->sum('disk');
            $memMax = $node->memory * (1 + ($node->memory_overallocate / 100));
            $diskMax = $node->disk * (1 + ($node->disk_overallocate / 100));

            $online = true;
            $sysInfo = [];
            try {
                $sysInfo = $this->daemonRepository->setNode($node)->getSystemInformation();
            } catch (\Exception $e) {
                $online = false;
            }

            return [
                'id' => $node->id,
                'name' => $node->name,
                'online' => $online,
                'maintenance' => $node->maintenance_mode,
                'servers' => $node->servers_count,
                'memory_used' => (int) $memUsed,
                'memory_percent' => $memMax > 0 ? round(($memUsed / $memMax) * 100, 1) : 0,
                'disk_used' => (int) $diskUsed,
                'disk_percent' => $diskMax > 0 ? round(($diskUsed / $diskMax) * 100, 1) : 0,
                'daemon_version' => $sysInfo['version'] ?? null,
            ];
        });

        return new JsonResponse($data);
    }
}
