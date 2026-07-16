@extends('layouts.admin')

@section('title')
    Activity Log
@endsection

@section('content-header')
    <h1>Activity Log<small>Global audit trail for all panel actions</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Activity Log</li>
    </ol>
@endsection

@section('content')

{{-- Filters --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-filter" style="margin-right:8px;color:var(--vh-accent);"></i>Filters</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('admin.activity') }}" method="GET" class="form-inline">
                    <div class="form-group" style="margin-right:15px;">
                        <label style="margin-right:5px;">Event:</label>
                        <input type="text" name="event" class="form-control input-sm" value="{{ $currentEvent }}" placeholder="e.g. server, auth, user...">
                    </div>
                    <div class="form-group" style="margin-right:15px;">
                        <label style="margin-right:5px;">IP Address:</label>
                        <input type="text" name="ip" class="form-control input-sm" value="{{ $currentIp }}" placeholder="e.g. 192.168.1.1">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search"></i> Search</button>
                    <a href="{{ route('admin.activity') }}" class="btn btn-sm btn-default"><i class="fa fa-times"></i> Clear</a>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Activity Table --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-history" style="margin-right:8px;color:var(--vh-accent);"></i>Activity Log ({{ $activities->total() }} entries)</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Event</th>
                            <th>Description</th>
                            <th>Actor</th>
                            <th>IP Address</th>
                            <th>Properties</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                        <tr>
                            <td style="color:var(--vh-text-muted);">{{ $activity->id }}</td>
                            <td>
                                @php
                                    $eventParts = explode(':', $activity->event);
                                    $eventIcon = match($eventParts[0] ?? '') {
                                        'auth' => 'fa-sign-in',
                                        'server' => 'fa-server',
                                        'user' => 'fa-user',
                                        'backup' => 'fa-archive',
                                        'file' => 'fa-file',
                                        'database' => 'fa-database',
                                        'schedule' => 'fa-clock-o',
                                        'allocation' => 'fa-plug',
                                        'subuser' => 'fa-users',
                                        default => 'fa-circle-o',
                                    };
                                    $eventColor = match($eventParts[0] ?? '') {
                                        'auth' => '#6c5ce7',
                                        'server' => '#2ed573',
                                        'user' => '#3742fa',
                                        'backup' => '#ffa502',
                                        'file' => '#a855f7',
                                        default => 'var(--vh-text-muted)',
                                    };
                                @endphp
                                <i class="fa {{ $eventIcon }}" style="color:{{ $eventColor }};margin-right:5px;"></i>
                                <code style="font-size:11px;">{{ $activity->event }}</code>
                            </td>
                            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $activity->description ?? '-' }}
                            </td>
                            <td>
                                @if($activity->actor)
                                    @if($activity->actor instanceof \Pterodactyl\Models\User)
                                        <a href="{{ route('admin.users.view', $activity->actor->id) }}">
                                            {{ $activity->actor->username ?? $activity->actor->email }}
                                        </a>
                                    @else
                                        {{ class_basename($activity->actor_type) }} #{{ $activity->actor_id }}
                                    @endif
                                @else
                                    <span style="color:var(--vh-text-muted);">System</span>
                                @endif
                            </td>
                            <td><code style="font-size:11px;">{{ $activity->ip }}</code></td>
                            <td style="max-width:150px;">
                                @if($activity->properties && $activity->properties->isNotEmpty())
                                    <button class="btn btn-xs btn-default" onclick="showProps({{ $activity->id }})" title="View Properties">
                                        <i class="fa fa-eye"></i> {{ $activity->properties->count() }} props
                                    </button>
                                    <div id="props-{{ $activity->id }}" style="display:none;">
                                        <pre style="font-size:10px;max-height:200px;overflow:auto;margin-top:5px;">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                @else
                                    <span style="color:var(--vh-text-muted);">-</span>
                                @endif
                            </td>
                            <td style="color:var(--vh-text-muted);font-size:12px;white-space:nowrap;">
                                <span title="{{ $activity->timestamp }}">{{ $activity->timestamp->diffForHumans() }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center;color:var(--vh-text-muted);padding:30px;">
                                No activity logs found matching your criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($activities->hasPages())
                <div class="box-footer with-border">
                    <div class="col-md-12 text-center">{!! $activities->appends(['event' => $currentEvent, 'ip' => $currentIp])->render() !!}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    function showProps(id) {
        var el = $('#props-' + id);
        el.toggle();
    }
    </script>
@endsection
