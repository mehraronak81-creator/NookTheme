@extends('layouts.admin')

@section('title')
    Admin Notes
@endsection

@section('content-header')
    <h1>Admin Notes<small>Internal todo & notes board</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Notes</li>
    </ol>
@endsection

@section('content')
<div class="row">
    {{-- Create Note --}}
    <div class="col-md-5">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-plus" style="margin-right:8px;color:var(--vh-accent);"></i>New Note</h3>
            </div>
            <form action="{{ route('admin.notes.store') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Note title..." required>
                    </div>
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" class="form-control" rows="4" placeholder="Details..." required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority" class="form-control">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category" class="form-control">
                                    <option value="general">General</option>
                                    <option value="security">Security</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="billing">Billing</option>
                                    <option value="bug">Bug</option>
                                    <option value="feature">Feature Request</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Note</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Notes List --}}
    <div class="col-md-7">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-sticky-note" style="margin-right:8px;color:var(--vh-accent);"></i>Notes ({{ count($notes) }})</h3>
            </div>
            <div class="box-body" style="max-height:700px;overflow-y:auto;">
                @forelse($notes as $note)
                <div class="note-card" style="background:var(--vh-surface);border:1px solid var(--vh-border);border-radius:10px;padding:15px;margin-bottom:12px;{{ $note['completed'] ? 'opacity:0.5;' : '' }}border-left:4px solid {{ $note['priority'] === 'high' ? 'var(--vh-danger)' : ($note['priority'] === 'medium' ? 'var(--vh-warning)' : 'var(--vh-success)') }};">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <div>
                            <strong style="color:var(--vh-text);font-size:15px;{{ $note['completed'] ? 'text-decoration:line-through;' : '' }}">{{ $note['title'] }}</strong>
                            <span class="label" style="margin-left:8px;background:{{ $note['priority'] === 'high' ? 'var(--vh-danger)' : ($note['priority'] === 'medium' ? 'var(--vh-warning)' : 'var(--vh-success)') }};font-size:10px;">{{ ucfirst($note['priority']) }}</span>
                            <span class="label" style="margin-left:4px;background:var(--vh-surface-2);color:var(--vh-text-secondary);font-size:10px;">{{ $note['category'] }}</span>
                        </div>
                        <div>
                            <form action="{{ route('admin.notes.toggle', $note['id']) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-xs {{ $note['completed'] ? 'btn-default' : 'btn-success' }}" title="{{ $note['completed'] ? 'Mark incomplete' : 'Mark complete' }}">
                                    <i class="fa fa-{{ $note['completed'] ? 'undo' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.notes.destroy', $note['id']) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    <p style="color:var(--vh-text-secondary);margin:0;white-space:pre-wrap;font-size:13px;">{{ $note['content'] }}</p>
                    <small style="color:var(--vh-text-muted);margin-top:8px;display:block;">By {{ $note['created_by'] }} &middot; {{ $note['created_at'] }}</small>
                </div>
                @empty
                <div style="text-align:center;padding:40px;color:var(--vh-text-muted);">
                    <i class="fa fa-sticky-note-o" style="font-size:48px;margin-bottom:12px;display:block;"></i>
                    No notes yet. Create your first one!
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
