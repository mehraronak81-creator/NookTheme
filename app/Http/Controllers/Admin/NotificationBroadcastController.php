<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Pterodactyl\Models\User;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use Pterodactyl\Http\Controllers\Controller;

class NotificationBroadcastController extends Controller
{
    /**
     * NotificationBroadcastController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected ViewFactory $view,
    ) {
    }

    /**
     * Show the broadcast form.
     */
    public function index(): View
    {
        return view('admin.notifications.index', [
            'userCount' => User::query()->count(),
            'adminCount' => User::query()->where('root_admin', true)->count(),
        ]);
    }

    /**
     * Send a notification to the selected audience. Rows are written directly
     * into the notifications table so they appear in each user's notification
     * center and bell badge.
     */
    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:191',
            'message' => 'required|string|max:2000',
            'level' => 'required|string|in:info,success,warning,error',
            'action_url' => 'nullable|url|max:2000',
            'audience' => 'required|string|in:all,admins',
        ]);

        $query = User::query();
        if ($data['audience'] === 'admins') {
            $query->where('root_admin', true);
        }

        $payload = [
            'title' => $data['title'],
            'message' => $data['message'],
            'level' => $data['level'],
            'action_url' => $data['action_url'] ?? null,
        ];

        $sent = 0;
        $query->chunkById(200, function ($users) use ($payload, &$sent) {
            foreach ($users as $user) {
                $user->notifications()->create([
                    'id' => Str::uuid()->toString(),
                    'type' => 'broadcast',
                    'data' => $payload,
                ]);
                ++$sent;
            }
        });

        $this->alert->success("Notification sent to {$sent} user(s).")->flash();

        return redirect()->route('admin.notifications');
    }
}
