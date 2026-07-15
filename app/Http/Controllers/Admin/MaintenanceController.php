<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\Node;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;

class MaintenanceController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert
    ) {
    }

    /**
     * Show maintenance mode overview.
     */
    public function index(): View
    {
        $nodes = Node::with('location')->withCount('servers')->get();

        return view('admin.maintenance.index', [
            'nodes' => $nodes,
        ]);
    }

    /**
     * Toggle maintenance mode for a node.
     */
    public function toggle(Node $node): RedirectResponse
    {
        $node->maintenance_mode = !$node->maintenance_mode;
        $node->save();

        $status = $node->maintenance_mode ? 'enabled' : 'disabled';
        $this->alert->success("Maintenance mode {$status} for node \"{$node->name}\".")->flash();

        return redirect()->route('admin.maintenance');
    }

    /**
     * Enable maintenance mode on all nodes.
     */
    public function enableAll(): RedirectResponse
    {
        Node::query()->update(['maintenance_mode' => true]);
        $this->alert->success('Maintenance mode enabled on all nodes.')->flash();

        return redirect()->route('admin.maintenance');
    }

    /**
     * Disable maintenance mode on all nodes.
     */
    public function disableAll(): RedirectResponse
    {
        Node::query()->update(['maintenance_mode' => false]);
        $this->alert->success('Maintenance mode disabled on all nodes.')->flash();

        return redirect()->route('admin.maintenance');
    }
}
