@extends('layouts.admin')

@section('title')
    Security Audit
@endsection

@section('content-header')
    <h1>Security Audit<small>Monitor & protect your panel</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Security Audit</li>
    </ol>
@endsection

@section('content')
{{-- Security Score Card --}}
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#2ed573,#17c964);border-radius:12px;">
            <div class="inner">
                <h3>{{ $usersWith2FA }}/{{ $usersTotal }}</h3>
                <p>Users with 2FA</p>
            </div>
            <div class="icon"><i class="fa fa-shield"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#ff4757,#ff6b81);border-radius:12px;">
            <div class="inner">
                <h3>{{ $failedLogins->count() }}</h3>
                <p>Failed Logins (7d)</p>
            </div>
            <div class="icon"><i class="fa fa-exclamation-triangle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#ffa502,#ff9f43);border-radius:12px;">
            <div class="inner">
                <h3>{{ $suspiciousIps->count() }}</h3>
                <p>Suspicious IPs (24h)</p>
            </div>
            <div class="icon"><i class="fa fa-ban"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#6c5ce7,#a855f7);border-radius:12px;">
            <div class="inner">
                <h3>{{ $adminUsers->count() }}</h3>
                <p>Admin Users</p>
            </div>
            <div class="icon"><i class="fa fa-user-secret"></i></div>
        </div>
    </div>
</div>

{{-- Suspicious IPs --}}
@if($suspiciousIps->count() > 0)
<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-warning" style="margin-right:8px;color:var(--vh-danger);"></i>Suspicious IPs (3+ failed logins in 24h)</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead><tr><th>IP Address</th><th>Failed Attempts</th><th>Action</th></tr></thead>
                    <tbody>
                    @foreach($suspiciousIps as $sus)
                        <tr>
                            <td><code>{{ $sus->ip }}</code></td>
                            <td><span class="label" style="background:var(--vh-danger);">{{ $sus->attempt_count }}</span></td>
                            <td>
                                <form action="{{ route('admin.security.ip-ban.store') }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="ip" value="{{ $sus->ip }}">
                                    <input type="hidden" name="reason" value="Auto-flagged: suspicious login activity">
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-ban"></i> Ban IP</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    {{-- Users Without 2FA --}}
    <div class="col-md-6">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-unlock-alt" style="margin-right:8px;color:var(--vh-warning);"></i>Users Without 2FA ({{ $usersWithout2FA->count() }})</h3>
            </div>
            <div class="box-body table-responsive no-padding" style="max-height:400px;overflow-y:auto;">
                <table class="table table-hover">
                    <thead><tr><th>Username</th><th>Email</th><th>Admin</th><th>Last Updated</th></tr></thead>
                    <tbody>
                    @foreach($usersWithout2FA as $user)
                        <tr>
                            <td><a href="{{ route('admin.users.view', $user->id) }}">{{ $user->username }}</a></td>
                            <td>{{ $user->email }}</td>
                            <td>{!! $user->root_admin ? '<span class="label" style="background:var(--vh-danger);">ADMIN</span>' : '<span class="label label-default">User</span>' !!}</td>
                            <td>{{ $user->updated_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Admin Users --}}
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-user-secret" style="margin-right:8px;color:var(--vh-accent);"></i>Admin Users</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead><tr><th>Username</th><th>Email</th><th>2FA</th><th>Created</th></tr></thead>
                    <tbody>
                    @foreach($adminUsers as $admin)
                        <tr>
                            <td><a href="{{ route('admin.users.view', $admin->id) }}">{{ $admin->username }}</a></td>
                            <td>{{ $admin->email }}</td>
                            <td>{!! $admin->use_totp ? '<span class="label" style="background:var(--vh-success);">Enabled</span>' : '<span class="label" style="background:var(--vh-danger);">Disabled</span>' !!}</td>
                            <td>{{ $admin->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- API Keys --}}
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-key" style="margin-right:8px;color:var(--vh-accent);"></i>API Keys ({{ $apiKeys->count() }})</h3>
            </div>
            <div class="box-body table-responsive no-padding" style="max-height:400px;overflow-y:auto;">
                <table class="table table-hover">
                    <thead><tr><th>Key</th><th>Owner</th><th>Last Used</th><th>Action</th></tr></thead>
                    <tbody>
                    @foreach($apiKeys as $key)
                        <tr>
                            <td><code>{{ substr($key->identifier, 0, 8) }}...</code></td>
                            <td>{{ $key->user->username ?? 'N/A' }}</td>
                            <td>{{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never' }}</td>
                            <td>
                                <button class="btn btn-xs btn-danger" onclick="revokeKey({{ $key->id }})"><i class="fa fa-times"></i></button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent Auth Events --}}
    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-history" style="margin-right:8px;color:var(--vh-accent);"></i>Recent Auth Events</h3>
            </div>
            <div class="box-body table-responsive no-padding" style="max-height:400px;overflow-y:auto;">
                <table class="table table-hover">
                    <thead><tr><th>Event</th><th>IP</th><th>User</th><th>Time</th></tr></thead>
                    <tbody>
                    @foreach($recentAuthEvents as $event)
                        <tr>
                            <td><code>{{ $event->event }}</code></td>
                            <td><code>{{ $event->ip }}</code></td>
                            <td>{{ $event->actor->username ?? 'System' }}</td>
                            <td>{{ $event->timestamp->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function revokeKey(id) {
    if (!confirm('Revoke this API key?')) return;
    $.ajax({
        url: '/admin/security/api-key/' + id,
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')},
        success: function() { location.reload(); }
    });
}
</script>
@endsection
