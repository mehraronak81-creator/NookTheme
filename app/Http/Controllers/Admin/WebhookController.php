<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;

class WebhookController extends Controller
{
    public function __construct(private AlertsMessageBag $alert)
    {
    }

    /**
     * List webhooks.
     */
    public function index(): View
    {
        $webhooks = Cache::get('admin_webhooks', []);
        return view('admin.webhooks.index', ['webhooks' => $webhooks]);
    }

    /**
     * Store new webhook.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'url' => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:server.created,server.deleted,server.suspended,user.created,user.deleted,node.offline,backup.completed',
        ]);

        $webhooks = Cache::get('admin_webhooks', []);
        $id = uniqid('wh_');

        $webhooks[$id] = [
            'id' => $id,
            'name' => $request->input('name'),
            'url' => $request->input('url'),
            'events' => $request->input('events'),
            'active' => true,
            'secret' => bin2hex(random_bytes(16)),
            'created_by' => $request->user()->username,
            'created_at' => now()->toDateTimeString(),
            'last_triggered' => null,
            'trigger_count' => 0,
        ];

        Cache::put('admin_webhooks', $webhooks, now()->addYear());
        $this->alert->success('Webhook created successfully.')->flash();
        return redirect()->route('admin.webhooks');
    }

    /**
     * Toggle webhook active state.
     */
    public function toggle(string $id): RedirectResponse
    {
        $webhooks = Cache::get('admin_webhooks', []);
        if (isset($webhooks[$id])) {
            $webhooks[$id]['active'] = !$webhooks[$id]['active'];
            Cache::put('admin_webhooks', $webhooks, now()->addYear());
        }
        $this->alert->success('Webhook toggled.')->flash();
        return redirect()->route('admin.webhooks');
    }

    /**
     * Test webhook by sending a test payload.
     */
    public function test(string $id): \Illuminate\Http\JsonResponse
    {
        $webhooks = Cache::get('admin_webhooks', []);
        if (!isset($webhooks[$id])) {
            return new \Illuminate\Http\JsonResponse(['error' => 'Webhook not found'], 404);
        }

        $webhook = $webhooks[$id];
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Webhook-Secret' => $webhook['secret'],
                    'X-Webhook-Event' => 'test',
                    'Content-Type' => 'application/json',
                ])
                ->post($webhook['url'], [
                    'event' => 'test',
                    'timestamp' => now()->toIso8601String(),
                    'data' => ['message' => 'This is a test webhook from VantaHost'],
                ]);

            return new \Illuminate\Http\JsonResponse([
                'success' => $response->successful(),
                'status' => $response->status(),
            ]);
        } catch (\Exception $e) {
            return new \Illuminate\Http\JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete webhook.
     */
    public function destroy(string $id): RedirectResponse
    {
        $webhooks = Cache::get('admin_webhooks', []);
        unset($webhooks[$id]);
        Cache::put('admin_webhooks', $webhooks, now()->addYear());
        $this->alert->success('Webhook deleted.')->flash();
        return redirect()->route('admin.webhooks');
    }
}
