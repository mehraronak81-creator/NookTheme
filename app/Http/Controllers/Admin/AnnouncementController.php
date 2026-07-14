<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;

class AnnouncementController extends Controller
{
    public function __construct(private AlertsMessageBag $alert)
    {
    }

    /**
     * Show announcements management page.
     */
    public function index(): View
    {
        $announcements = Cache::get('admin_announcements', []);
        // Sort by created_at desc
        usort($announcements, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        return view('admin.announcements.index', [
            'announcements' => $announcements,
        ]);
    }

    /**
     * Create a new announcement.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'type' => 'required|in:info,warning,danger,success',
            'active' => 'nullable|boolean',
        ]);

        $announcements = Cache::get('admin_announcements', []);
        $id = uniqid('ann_');

        $announcements[] = [
            'id' => $id,
            'title' => $request->input('title'),
            'message' => $request->input('message'),
            'type' => $request->input('type'),
            'active' => $request->boolean('active', true),
            'created_by' => $request->user()->username,
            'created_at' => now()->toDateTimeString(),
        ];

        Cache::put('admin_announcements', $announcements, now()->addYear());
        $this->alert->success('Announcement created successfully.')->flash();
        return redirect()->route('admin.announcements');
    }

    /**
     * Toggle announcement active status.
     */
    public function toggle(string $id): RedirectResponse
    {
        $announcements = Cache::get('admin_announcements', []);
        foreach ($announcements as &$ann) {
            if ($ann['id'] === $id) {
                $ann['active'] = !$ann['active'];
                break;
            }
        }
        Cache::put('admin_announcements', $announcements, now()->addYear());
        $this->alert->success('Announcement toggled.')->flash();
        return redirect()->route('admin.announcements');
    }

    /**
     * Delete announcement.
     */
    public function destroy(string $id): RedirectResponse
    {
        $announcements = Cache::get('admin_announcements', []);
        $announcements = array_values(array_filter($announcements, fn($a) => $a['id'] !== $id));
        Cache::put('admin_announcements', $announcements, now()->addYear());
        $this->alert->success('Announcement deleted.')->flash();
        return redirect()->route('admin.announcements');
    }

    /**
     * Get active announcements as JSON (for frontend banner).
     */
    public function active(): \Illuminate\Http\JsonResponse
    {
        $announcements = Cache::get('admin_announcements', []);
        $active = array_values(array_filter($announcements, fn($a) => $a['active']));
        return new \Illuminate\Http\JsonResponse($active);
    }
}
