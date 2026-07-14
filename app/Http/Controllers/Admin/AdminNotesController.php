<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;

class AdminNotesController extends Controller
{
    public function __construct(private AlertsMessageBag $alert)
    {
    }

    /**
     * Show admin notes/todo page.
     */
    public function index(): View
    {
        $notes = Cache::get('admin_notes', []);
        usort($notes, function ($a, $b) {
            $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
            $pa = $priorityOrder[$a['priority']] ?? 2;
            $pb = $priorityOrder[$b['priority']] ?? 2;
            if ($pa !== $pb) return $pa - $pb;
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return view('admin.notes.index', ['notes' => $notes]);
    }

    /**
     * Create note.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'priority' => 'required|in:low,medium,high',
            'category' => 'nullable|string|max:50',
        ]);

        $notes = Cache::get('admin_notes', []);
        $notes[] = [
            'id' => uniqid('note_'),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'priority' => $request->input('priority'),
            'category' => $request->input('category', 'general'),
            'completed' => false,
            'created_by' => $request->user()->username,
            'created_at' => now()->toDateTimeString(),
        ];

        Cache::put('admin_notes', $notes, now()->addYear());
        $this->alert->success('Note created.')->flash();
        return redirect()->route('admin.notes');
    }

    /**
     * Toggle completed.
     */
    public function toggle(string $id): RedirectResponse
    {
        $notes = Cache::get('admin_notes', []);
        foreach ($notes as &$note) {
            if ($note['id'] === $id) {
                $note['completed'] = !$note['completed'];
                break;
            }
        }
        Cache::put('admin_notes', $notes, now()->addYear());
        return redirect()->route('admin.notes');
    }

    /**
     * Delete note.
     */
    public function destroy(string $id): RedirectResponse
    {
        $notes = Cache::get('admin_notes', []);
        $notes = array_values(array_filter($notes, fn($n) => $n['id'] !== $id));
        Cache::put('admin_notes', $notes, now()->addYear());
        $this->alert->success('Note deleted.')->flash();
        return redirect()->route('admin.notes');
    }
}
