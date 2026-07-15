@extends('layouts.admin')

@section('title')
    IP Ban Manager
@endsection

@section('content-header')
    <h1>IP Ban Manager<small>Block malicious IPs</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.security') }}">Security</a></li>
        <li class="active">IP Bans</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#ff4757,#ff6b81);border-radius:12px;">
            <div class="inner">
                <h3>{{ count($bannedIps) }}</h3>
                <p>Manually Banned</p>
            </div>
            <div class="icon"><i class="fa fa-ban"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#ffa502,#ff9f43);border-radius:12px;">
            <div class="inner">
                <h3>{{ count($autoBlocked) }}</h3>
                <p>Auto-Blocked</p>
            </div>
            <div class="icon"><i class="fa fa-robot"></i></div>
        </div>
    </div>
</div>

{{-- Add IP Ban Form --}}
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-plus" style="margin-right:8px;color:var(--vh-accent);"></i>Ban IP Address</h3>
            </div>
            <form action="{{ route('admin.security.ip-ban.store') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label>IP Address</label>
                        <input type="text" name="ip" class="form-control" placeholder="e.g. 192.168.1.100" required>
                    </div>
                    <div class="form-group">
                        <label>Reason</label>
                        <input type="text" name="reason" class="form-control" placeholder="Reason for ban">
                    </div>
                    <div class="form-group">
                        <label>Duration (minutes, 0 = permanent)</label>
                        <input type="number" name="duration" class="form-control" value="0" min="0">
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-danger"><i class="fa fa-ban"></i> Ban IP</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Banned IPs List --}}
<div class="row">
    <div class="col-md-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list" style="margin-right:8px;"></i>Manually Banned IPs</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead><tr><th>IP</th><th>Reason</th><th>Banned By</th><th>Banned At</th><th>Expires</th><th>Action</th></tr></thead>
                    <tbody>
                    @forelse($bannedIps as $ban)
                        <tr>
                            <td><code>{{ $ban['ip'] }}</code></td>
                            <td>{{ $ban['reason'] ?? '-' }}</td>
                            <td>{{ $ban['banned_by'] ?? '-' }}</td>
                            <td>{{ $ban['banned_at'] }}</td>
                            <td>{{ $ban['expires_at'] ?? 'Permanent' }}</td>
                            <td>
                                <form action="{{ route('admin.security.ip-ban.destroy', $ban['ip']) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-success"><i class="fa fa-check"></i> Unban</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center" style="color:var(--vh-text-muted);">No manually banned IPs</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Auto-Blocked IPs --}}
<div class="row">
    <div class="col-md-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-shield" style="margin-right:8px;"></i>Auto-Blocked IPs</h3>
                @if(count($autoBlocked) > 0)
                <div class="box-tools pull-right">
                    <form action="{{ route('admin.security.ip-ban.clear-auto') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-warning"><i class="fa fa-trash"></i> Clear All</button>
                    </form>
                </div>
                @endif
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead><tr><th>IP</th><th>Reason</th><th>Blocked At</th><th>Violations</th><th>Action</th></tr></thead>
                    <tbody>
                    @forelse($autoBlocked as $block)
                        <tr>
                            <td><code>{{ $block['ip'] }}</code></td>
                            <td>{{ $block['reason'] }}</td>
                            <td>{{ $block['blocked_at'] }}</td>
                            <td><span class="label" style="background:var(--vh-danger);">{{ $block['violations'] ?? '?' }}</span></td>
                            <td>
                                <form action="{{ route('admin.security.ip-ban.destroy', $block['ip']) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-success"><i class="fa fa-check"></i> Unblock</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center" style="color:var(--vh-text-muted);">No auto-blocked IPs</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
