<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Models\Announcement;
use Illuminate\View\Factory as ViewFactory;
use Pterodactyl\Http\Controllers\Controller;

class AnnouncementController extends Controller
{
    /**
     * AnnouncementController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected ViewFactory $view,
    ) {
    }

    /**
     * Display a list of all announcements on the system.
     */
    public function index(): View
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::query()->orderByDesc('priority')->orderByDesc('id')->get(),
        ]);
    }

    /**
     * Display the form used to edit an existing announcement.
     */
    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', [
            'announcement' => $announcement,
        ]);
    }

    /**
     * Store a new announcement in the database.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Announcement::query()->create($data);

        $this->alert->success('Announcement was created successfully.')->flash();

        return redirect()->route('admin.announcements');
    }

    /**
     * Update an existing announcement, or delete it if requested.
     */
    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        if ($request->input('action') === 'delete') {
            return $this->delete($announcement);
        }

        $announcement->update($this->validated($request));

        $this->alert->success('Announcement was updated successfully.')->flash();

        return redirect()->route('admin.announcements.edit', $announcement->id);
    }

    /**
     * Delete an announcement from the system.
     */
    public function delete(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        $this->alert->success('Announcement was deleted.')->flash();

        return redirect()->route('admin.announcements');
    }

    /**
     * Validate and normalize the incoming request payload.
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate(Announcement::$validationRules);

        // Checkboxes are absent from the payload when unchecked.
        $data['active'] = $request->boolean('active');
        $data['priority'] = (int) $request->input('priority', 0);
        $data['starts_at'] = $request->input('starts_at') ?: null;
        $data['ends_at'] = $request->input('ends_at') ?: null;

        return $data;
    }
}
