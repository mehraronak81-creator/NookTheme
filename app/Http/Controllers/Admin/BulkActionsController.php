<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\Server;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Servers\SuspensionService;
use Pterodactyl\Services\Servers\ReinstallServerService;

class BulkActionsController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SuspensionService $suspensionService,
        private ReinstallServerService $reinstallService
    ) {
    }

    /**
     * Show the bulk actions page.
     */
    public function index(Request $request): View
    {
        $query = Server::with(['node', 'user', 'allocation']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->whereNull('status');
            } elseif ($request->input('status') === 'suspended') {
                $query->where('status', Server::STATUS_SUSPENDED);
            } elseif ($request->input('status') === 'installing') {
                $query->where('status', Server::STATUS_INSTALLING);
            } elseif ($request->input('status') === 'failed') {
                $query->whereIn('status', [Server::STATUS_INSTALL_FAILED, Server::STATUS_REINSTALL_FAILED]);
            }
        }

        // Filter by node
        if ($request->filled('node')) {
            $query->where('node_id', $request->input('node'));
        }

        $servers = $query->orderBy('name')->paginate(50);
        $nodes = Node::orderBy('name')->get();

        return view('admin.bulk-actions.index', [
            'servers' => $servers,
            'nodes' => $nodes,
            'currentStatus' => $request->input('status', ''),
            'currentNode' => $request->input('node', ''),
        ]);
    }

    /**
     * Execute bulk action on selected servers.
     */
    public function execute(Request $request): RedirectResponse
    {
        $action = $request->input('action');
        $serverIds = $request->input('servers', []);

        if (empty($serverIds)) {
            $this->alert->warning('No servers selected for bulk action.')->flash();
            return redirect()->route('admin.bulk-actions');
        }

        $servers = Server::whereIn('id', $serverIds)->get();
        $count = 0;
        $errors = 0;

        foreach ($servers as $server) {
            try {
                switch ($action) {
                    case 'suspend':
                        if (!$server->isSuspended()) {
                            $this->suspensionService->toggle($server, SuspensionService::ACTION_SUSPEND);
                            $count++;
                        }
                        break;

                    case 'unsuspend':
                        if ($server->isSuspended()) {
                            $this->suspensionService->toggle($server, SuspensionService::ACTION_UNSUSPEND);
                            $count++;
                        }
                        break;

                    case 'reinstall':
                        if ($server->isInstalled()) {
                            $this->reinstallService->handle($server);
                            $count++;
                        }
                        break;

                    default:
                        $this->alert->danger('Unknown action: ' . $action)->flash();
                        return redirect()->route('admin.bulk-actions');
                }
            } catch (\Exception $e) {
                $errors++;
            }
        }

        $actionName = ucfirst($action);
        $message = "{$actionName} completed on {$count} server(s).";
        if ($errors > 0) {
            $message .= " {$errors} error(s) occurred.";
        }

        $this->alert->success($message)->flash();
        return redirect()->route('admin.bulk-actions');
    }
}
