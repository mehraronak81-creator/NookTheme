<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;

class IpBanController extends Controller
{
    public function __construct(private AlertsMessageBag $alert)
    {
    }

    /**
     * Show IP ban management page.
     */
    public function index(): View
    {
        $bannedIps = Cache::get('admin_banned_ips', []);
        $autoBlocked = Cache::get('admin_auto_blocked_ips', []);

        return view('admin.security.ip-ban', [
            'bannedIps' => $bannedIps,
            'autoBlocked' => $autoBlocked,
        ]);
    }

    /**
     * Ban an IP address.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'ip' => 'required|ip',
            'reason' => 'nullable|string|max:255',
            'duration' => 'nullable|integer|min:0',
        ]);

        $bannedIps = Cache::get('admin_banned_ips', []);
        $ip = $request->input('ip');

        // Don't allow banning own IP
        if ($ip === $request->ip()) {
            $this->alert->danger('You cannot ban your own IP address.')->flash();
            return redirect()->route('admin.security.ip-ban');
        }

        $bannedIps[$ip] = [
            'ip' => $ip,
            'reason' => $request->input('reason', 'Manual ban'),
            'banned_by' => $request->user()->username,
            'banned_at' => now()->toDateTimeString(),
            'expires_at' => $request->input('duration') > 0
                ? now()->addMinutes($request->input('duration'))->toDateTimeString()
                : null,
        ];

        Cache::put('admin_banned_ips', $bannedIps, now()->addYear());
        $this->alert->success("IP {$ip} has been banned.")->flash();
        return redirect()->route('admin.security.ip-ban');
    }

    /**
     * Unban an IP address.
     */
    public function destroy(Request $request, string $ip): RedirectResponse
    {
        $bannedIps = Cache::get('admin_banned_ips', []);
        unset($bannedIps[$ip]);
        Cache::put('admin_banned_ips', $bannedIps, now()->addYear());

        // Also remove from auto-blocked
        $autoBlocked = Cache::get('admin_auto_blocked_ips', []);
        unset($autoBlocked[$ip]);
        Cache::put('admin_auto_blocked_ips', $autoBlocked, now()->addYear());

        $this->alert->success("IP {$ip} has been unbanned.")->flash();
        return redirect()->route('admin.security.ip-ban');
    }

    /**
     * Clear all auto-blocked IPs.
     */
    public function clearAutoBlocked(): RedirectResponse
    {
        Cache::forget('admin_auto_blocked_ips');
        $this->alert->success('All auto-blocked IPs have been cleared.')->flash();
        return redirect()->route('admin.security.ip-ban');
    }
}
