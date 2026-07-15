@extends('layouts.admin')

@section('title')
    Announcements
@endsection

@section('content-header')
    <h1>Announcements<small>Broadcast messages to users</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Announcements</li>
    </ol>
@endsection

@section('content')
{{-- Create Announcement --}}
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bullhorn" style="margin-right:8px;color:var(--vh-accent);"></i>Create Announcement</h3>
            </div>
            <form action="{{ route('admin.announcements.store') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Announcement title" required>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Announcement message..." required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type</label>
                                <select name="type" class="form-control">
                                    <option value="info">Info (Blue)</option>
                                    <option value="success">Success (Green)</option>
                                    <option value="warning">Warning (Orange)</option>
                                    <option value="danger">Danger (Red)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Active</label>
                                <select name="active" class="form-control">
                                    <option value="1">Yes - Show immediately</option>
                                    <option value="0">No - Save as draft</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Create Announcement</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle" style="margin-right:8px;"></i>Info</h3>
            </div>
            <div class="box-body">
                <p style="color:var(--vh-text-secondary);">Announcements are displayed as banners to all users when they visit the panel. Use them for maintenance notices, updates, or important information.</p>
                <p style="color:var(--vh-text-secondary);">Total: <strong>{{ count($announcements) }}</strong></p>
                <p style="color:var(--vh-text-secondary);">Active: <strong>{{ count(array_filter($announcements, fn($a) => $a['active'])) }}</strong></p>
            </div>
        </div>
    </div>
</div>

{{-- Existing Announcements --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list" style="margin-right:8px;"></i>All Announcements</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead><tr><th>Status</th><th>Type</th><th>Title</th><th>Created By</th><th>Created At</th><th>Actions</th></tr></thead>
                    <tbody>
                    @forelse($announcements as $ann)
                        <tr>
                            <td>
                                @if($ann['active'])
                                    <span class="label" style="background:var(--vh-success);">Active</span>
                                @else
                                    <span class="label label-default">Draft</span>
                                @endif
                            </td>
                            <td><span class="label label-{{ $ann['type'] }}">{{ ucfirst($ann['type']) }}</span></td>
                            <td><strong>{{ $ann['title'] }}</strong><br><small style="color:var(--vh-text-muted);">{{ \Illuminate\Support\Str::limit($ann['message'], 80) }}</small></td>
                            <td>{{ $ann['created_by'] }}</td>
                            <td>{{ $ann['created_at'] }}</td>
                            <td>
                                <form action="{{ route('admin.announcements.toggle', $ann['id']) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-xs {{ $ann['active'] ? 'btn-warning' : 'btn-success' }}">
                                        <i class="fa fa-{{ $ann['active'] ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.announcements.destroy', $ann['id']) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Delete this announcement?')"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center" style="color:var(--vh-text-muted);">No announcements yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
