<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Servers\SuspensionService;
use Pterodactyl\Services\Servers\ServerDeletionService;

class TrashBinController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SuspensionService $suspensionService,
        private ServerDeletionService $deletionService
    ) {
    }

    /**
     * Show all suspended servers (trash bin).
     */
    public function index(Request $request): View
    {
        $servers = Server::where('status', Server::STATUS_SUSPENDED)
            ->with(['node', 'user', 'allocation', 'nest', 'egg'])
            ->orderBy('updated_at', 'desc')
            ->paginate(25);

        return view('admin.trashbin.index', ['servers' => $servers]);
    }

    /**
     * Unsuspend (restore) a server from trash.
     */
    public function restore(Server $server): RedirectResponse
    {
        $this->suspensionService->toggle($server, SuspensionService::ACTION_UNSUSPEND);
        $this->alert->success('Server "' . $server->name . '" has been restored from trash.')->flash();

        return redirect()->route('admin.trashbin');
    }

    /**
     * Bulk restore multiple servers.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $ids = $request->input('servers', []);
        if (empty($ids)) {
            $this->alert->warning('No servers selected.')->flash();
            return redirect()->route('admin.trashbin');
        }

        $count = 0;
        foreach ($ids as $id) {
            $server = Server::find($id);
            if ($server && $server->isSuspended()) {
                try {
                    $this->suspensionService->toggle($server, SuspensionService::ACTION_UNSUSPEND);
                    $count++;
                } catch (\Exception $e) {
                    // Continue with others
                }
            }
        }

        $this->alert->success("Successfully restored {$count} server(s) from trash.")->flash();
        return redirect()->route('admin.trashbin');
    }

    /**
     * Permanently delete a server from trash.
     */
    public function destroy(Server $server): RedirectResponse
    {
        try {
            $name = $server->name;
            $this->deletionService->handle($server);
            $this->alert->success('Server "' . $name . '" has been permanently deleted.')->flash();
        } catch (\Exception $e) {
            $this->alert->danger('Failed to delete server: ' . $e->getMessage())->flash();
        }

        return redirect()->route('admin.trashbin');
    }

    /**
     * Permanently delete multiple servers (empty trash).
     */
    public function emptyTrash(Request $request): RedirectResponse
    {
        $ids = $request->input('servers', []);
        $deleteAll = $request->input('delete_all', false);

        if ($deleteAll) {
            $servers = Server::where('status', Server::STATUS_SUSPENDED)->get();
        } else {
            $servers = Server::whereIn('id', $ids)->where('status', Server::STATUS_SUSPENDED)->get();
        }

        if ($servers->isEmpty()) {
            $this->alert->warning('No suspended servers found to delete.')->flash();
            return redirect()->route('admin.trashbin');
        }

        $count = 0;
        foreach ($servers as $server) {
            try {
                $this->deletionService->handle($server);
                $count++;
            } catch (\Exception $e) {
                // Continue with others
            }
        }

        $this->alert->success("Permanently deleted {$count} server(s).")->flash();
        return redirect()->route('admin.trashbin');
    }
}
