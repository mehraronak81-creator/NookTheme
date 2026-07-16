<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\ActivityLog;
use Pterodactyl\Http\Controllers\Controller;

class ActivityController extends Controller
{
    /**
     * Show the activity log page.
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::with('actor')->orderBy('timestamp', 'desc');

        // Filter by event type
        if ($request->filled('event')) {
            $query->where('event', 'like', '%' . $request->input('event') . '%');
        }

        // Filter by IP
        if ($request->filled('ip')) {
            $query->where('ip', $request->input('ip'));
        }

        $activities = $query->paginate(50);

        // Get unique event types for filter
        $eventTypes = ActivityLog::selectRaw('DISTINCT SUBSTRING_INDEX(event, ":", 1) as event_group')
            ->limit(50)
            ->pluck('event_group')
            ->unique()
            ->sort()
            ->values();

        return view('admin.activity.index', [
            'activities' => $activities,
            'eventTypes' => $eventTypes,
            'currentEvent' => $request->input('event', ''),
            'currentIp' => $request->input('ip', ''),
        ]);
    }
}
