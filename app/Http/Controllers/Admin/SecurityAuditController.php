<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\User;
use Pterodactyl\Models\ApiKey;
use Pterodactyl\Models\ActivityLog;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SecurityAuditController extends Controller
{
    /**
     * Security Audit Dashboard - shows login attempts, suspicious IPs, 2FA status, API key audit.
     */
    public function index(Request $request): View
    {
        // Users without 2FA
        $usersWithout2FA = User::where('use_totp', false)->orderBy('updated_at', 'desc')->get();
        $usersTotal = User::count();
        $usersWith2FA = User::where('use_totp', true)->count();

        // Failed login attempts (last 7 days)
        $failedLogins = ActivityLog::where('event', 'like', '%failed%')
            ->where('timestamp', '>=', now()->subDays(7))
            ->orderBy('timestamp', 'desc')
            ->limit(100)
            ->get();

        // Suspicious IPs: IPs with 3+ failed logins in last 24h
        $suspiciousIps = ActivityLog::where('event', 'like', '%failed%')
            ->where('timestamp', '>=', now()->subDay())
            ->selectRaw('ip, COUNT(*) as attempt_count')
            ->groupBy('ip')
            ->having('attempt_count', '>=', 3)
            ->orderByRaw('attempt_count DESC')
            ->get();

        // Active sessions from the sessions table
        $activeSessions = collect();
        try {
            $activeSessions = DB::table('sessions')
                ->where('last_activity', '>=', now()->subHours(2)->timestamp)
                ->orderBy('last_activity', 'desc')
                ->limit(50)
                ->get();
        } catch (\Exception $e) {
            // Sessions table may not exist if using different session driver
        }

        // API keys overview
        $apiKeys = ApiKey::with('user')
            ->orderBy('last_used_at', 'desc')
            ->limit(50)
            ->get();

        // Admin users list
        $adminUsers = User::where('root_admin', true)->get();

        // Recent auth events
        $recentAuthEvents = ActivityLog::where('event', 'like', 'auth:%')
            ->orderBy('timestamp', 'desc')
            ->limit(30)
            ->get();

        return view('admin.security.index', [
            'usersWithout2FA' => $usersWithout2FA,
            'usersTotal' => $usersTotal,
            'usersWith2FA' => $usersWith2FA,
            'failedLogins' => $failedLogins,
            'suspiciousIps' => $suspiciousIps,
            'activeSessions' => $activeSessions,
            'apiKeys' => $apiKeys,
            'adminUsers' => $adminUsers,
            'recentAuthEvents' => $recentAuthEvents,
        ]);
    }

    /**
     * Force-logout a user session.
     */
    public function destroySession(Request $request, string $sessionId): JsonResponse
    {
        DB::table('sessions')->where('id', $sessionId)->delete();
        return new JsonResponse(['success' => true, 'message' => 'Session terminated.']);
    }

    /**
     * Revoke an API key.
     */
    public function revokeApiKey(Request $request, int $id): JsonResponse
    {
        $key = ApiKey::findOrFail($id);
        $key->delete();
        return new JsonResponse(['success' => true, 'message' => 'API key revoked.']);
    }
}
