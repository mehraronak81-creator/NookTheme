@extends('layouts.admin')

@section('title')
    Backup Manager
@endsection

@section('content-header')
    <h1>Backup Manager<small>Global backup overview</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Backup Manager</li>
    </ol>
@endsection

@section('content')
{{-- Stats --}}
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#6c5ce7,#a855f7);border-radius:12px;">
            <div class="inner">
                <h3>{{ $totalBackups }}</h3>
                <p>Total Backups</p>
            </div>
            <div class="icon"><i class="fa fa-archive"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#2ed573,#17c964);border-radius:12px;">
            <div class="inner">
                <h3>{{ $completedBackups }}</h3>
                <p>Completed</p>
            </div>
            <div class="icon"><i class="fa fa-check"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#ff4757,#ff6b81);border-radius:12px;">
            <div class="inner">
                <h3>{{ $failedBackups }}</h3>
                <p>Failed</p>
            </div>
            <div class="icon"><i class="fa fa-times"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#3742fa,#5352ed);border-radius:12px;">
            <div class="inner">
                <h3>{{ number_format($totalSize / 1024 / 1024 / 1024, 2) }} GB</h3>
                <p>Total Size</p>
            </div>
            <div class="icon"><i class="fa fa-database"></i></div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-filter" style="margin-right:8px;"></i>Filters</h3>
            </div>
            <div class="box-body">
                <form method="GET" class="form-inline">
                    <div class="form-group" style="margin-right:15px;">
                        <label style="margin-right:5px;">Status:</label>
                        <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="">All</option>
                            <option value="completed" {{ $currentStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ $currentStatus === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Per-Node Backup Stats --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-sitemap" style="margin-right:8px;color:var(--vh-accent);"></i>Backup Usage by Node</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead><tr><th>Node</th><th>Backups</th><th>Total Size</th></tr></thead>
                    <tbody>
                    @foreach($nodeBackupStats as $ns)
                        <tr>
                            <td><strong>{{ $ns['name'] }}</strong></td>
                            <td>{{ $ns['backup_count'] }}</td>
                            <td>{{ number_format($ns['backup_size'] / 1024 / 1024, 2) }} MB</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- All Backups --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list" style="margin-right:8px;color:var(--vh-accent);"></i>All Backups</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead><tr><th>Status</th><th>Name</th><th>Server</th><th>Owner</th><th>Node</th><th>Size</th><th>Created</th><th>Completed</th></tr></thead>
                    <tbody>
                    @forelse($backups as $backup)
                        <tr>
                            <td>
                                @if($backup->completed_at && $backup->is_successful)
                                    <span class="label" style="background:var(--vh-success);">OK</span>
                                @elseif($backup->completed_at && !$backup->is_successful)
                                    <span class="label" style="background:var(--vh-danger);">Failed</span>
                                @else
                                    <span class="label" style="background:var(--vh-warning);">Pending</span>
                                @endif
                            </td>
                            <td>{{ $backup->name }}</td>
                            <td>
                                @if($backup->server)
                                    <a href="{{ route('admin.servers.view', $backup->server->id) }}">{{ $backup->server->name }}</a>
                                @else
                                    <em style="color:var(--vh-text-muted);">Deleted</em>
                                @endif
                            </td>
                            <td>{{ $backup->server->user->username ?? '-' }}</td>
                            <td>{{ $backup->server->node->name ?? '-' }}</td>
                            <td>{{ $backup->bytes ? number_format($backup->bytes / 1024 / 1024, 2) . ' MB' : '-' }}</td>
                            <td>{{ $backup->created_at->diffForHumans() }}</td>
                            <td>{{ $backup->completed_at ? $backup->completed_at->diffForHumans() : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center" style="color:var(--vh-text-muted);">No backups found</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($backups->hasPages())
            <div class="box-footer text-center">
                {{ $backups->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
