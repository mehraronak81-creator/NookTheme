@extends('layouts.admin')

@section('title')
    Administration
@endsection

@section('content-header')
    <h1>Administrative Overview<small>VantaHost Control Panel</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Index</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box
            @if($version->isLatestPanel())
                box-success
            @else
                box-danger
            @endif
        ">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle" style="margin-right:8px;color:var(--vh-accent,#6c5ce7);"></i>System Information</h3>
            </div>
            <div class="box-body">
                @if ($version->isLatestPanel())
                    You are running <strong style="color:var(--vh-accent,#6c5ce7);">VantaHost</strong> <code>{{ config('app.fork-version') }}</code> based on Pterodactyl Panel version <code>{{ config('app.version') }}</code>. Your panel is up-to-date!
                @else
                    Your panel is <strong>not up-to-date!</strong> The latest version is <a href="https://github.com/Pterodactyl/Panel/releases/v{{ $version->getPanel() }}" target="_blank"><code>{{ $version->getPanel() }}</code></a> and you are currently running version <code>{{ config('app.version') }}</code>.
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Quick Stats Cards --}}
<div class="row" id="system-health">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#6c5ce7,#a855f7);border-radius:12px;">
            <div class="inner">
                <h3 id="stat-servers">{{ $servers }}</h3>
                <p>Total Servers</p>
            </div>
            <div class="icon">
                <i class="fa fa-server"></i>
            </div>
            <a href="{{ route('admin.servers') }}" class="small-box-footer" style="color:rgba(255,255,255,0.8);">
                Manage Servers <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#2ed573,#17c964);border-radius:12px;">
            <div class="inner">
                <h3 id="stat-nodes">{{ $nodes }}</h3>
                <p>Total Nodes</p>
            </div>
            <div class="icon">
                <i class="fa fa-sitemap"></i>
            </div>
            <a href="{{ route('admin.nodes') }}" class="small-box-footer" style="color:rgba(255,255,255,0.8);">
                Manage Nodes <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#3742fa,#5352ed);border-radius:12px;">
            <div class="inner">
                <h3 id="stat-users">{{ $users }}</h3>
                <p>Total Users</p>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
            <a href="{{ route('admin.users') }}" class="small-box-footer" style="color:rgba(255,255,255,0.8);">
                Manage Users <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#ffa502,#ff7f50);border-radius:12px;">
            <div class="inner">
                <h3 id="stat-eggs">{{ $eggs }}</h3>
                <p>Total Eggs</p>
            </div>
            <div class="icon">
                <i class="fa fa-th-large"></i>
            </div>
            <a href="{{ route('admin.nests') }}" class="small-box-footer" style="color:rgba(255,255,255,0.8);">
                Manage Nests <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- Server Status Breakdown --}}
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:var(--vh-surface-2,#1a1a28);border:1px solid var(--vh-border,#2a2a3a);border-radius:12px;">
            <div class="inner">
                <h3 style="color:var(--vh-success,#2ed573);" id="stat-active">{{ $activeServers }}</h3>
                <p style="color:var(--vh-text-secondary,#b0b0c0);">Active Servers</p>
            </div>
            <div class="icon"><i class="fa fa-check-circle" style="color:var(--vh-success,#2ed573);"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:var(--vh-surface-2,#1a1a28);border:1px solid var(--vh-border,#2a2a3a);border-radius:12px;">
            <div class="inner">
                <h3 style="color:var(--vh-danger,#ff4757);" id="stat-suspended">{{ $suspendedServers }}</h3>
                <p style="color:var(--vh-text-secondary,#b0b0c0);">Suspended <small>(<a href="{{ route('admin.trashbin') }}">Trash Bin</a>)</small></p>
            </div>
            <div class="icon"><i class="fa fa-ban" style="color:var(--vh-danger,#ff4757);"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:var(--vh-surface-2,#1a1a28);border:1px solid var(--vh-border,#2a2a3a);border-radius:12px;">
            <div class="inner">
                <h3 style="color:var(--vh-warning,#ffa502);" id="stat-installing">{{ $installingServers }}</h3>
                <p style="color:var(--vh-text-secondary,#b0b0c0);">Installing</p>
            </div>
            <div class="icon"><i class="fa fa-spinner" style="color:var(--vh-warning,#ffa502);"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:var(--vh-surface-2,#1a1a28);border:1px solid var(--vh-border,#2a2a3a);border-radius:12px;">
            <div class="inner">
                <h3 style="color:#ff6348;" id="stat-failed">{{ $failedServers }}</h3>
                <p style="color:var(--vh-text-secondary,#b0b0c0);">Failed Installs</p>
            </div>
            <div class="icon"><i class="fa fa-exclamation-triangle" style="color:#ff6348;"></i></div>
        </div>
    </div>
</div>

{{-- Resource Usage --}}
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-microchip" style="margin-right:8px;color:var(--vh-accent,#6c5ce7);"></i>Memory Allocation</h3>
                <span class="pull-right label" style="background:var(--vh-accent);" id="mem-badge">{{ $totalMemory > 0 ? round(($allocatedMemory / $totalMemory) * 100, 1) : 0 }}%</span>
            </div>
            <div class="box-body">
                <div style="background:var(--vh-surface,#12121a);border-radius:8px;overflow:hidden;height:28px;margin-bottom:10px;">
                    <div id="mem-bar" style="height:100%;background:linear-gradient(90deg,#6c5ce7,#a855f7);border-radius:8px;transition:width 0.6s ease;width:{{ $totalMemory > 0 ? round(($allocatedMemory / $totalMemory) * 100, 1) : 0 }}%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:600;">
                        {{ $totalMemory > 0 ? round(($allocatedMemory / $totalMemory) * 100, 1) : 0 }}%
                    </div>
                </div>
                <p style="margin:0;color:var(--vh-text-secondary);">
                    <strong id="mem-used">{{ number_format($allocatedMemory) }}</strong> MiB allocated of <strong>{{ number_format($totalMemory) }}</strong> MiB total
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-hdd-o" style="margin-right:8px;color:var(--vh-accent,#6c5ce7);"></i>Disk Allocation</h3>
                <span class="pull-right label" style="background:var(--vh-accent);" id="disk-badge">{{ $totalDisk > 0 ? round(($allocatedDisk / $totalDisk) * 100, 1) : 0 }}%</span>
            </div>
            <div class="box-body">
                <div style="background:var(--vh-surface,#12121a);border-radius:8px;overflow:hidden;height:28px;margin-bottom:10px;">
                    <div id="disk-bar" style="height:100%;background:linear-gradient(90deg,#2ed573,#17c964);border-radius:8px;transition:width 0.6s ease;width:{{ $totalDisk > 0 ? round(($allocatedDisk / $totalDisk) * 100, 1) : 0 }}%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:600;">
                        {{ $totalDisk > 0 ? round(($allocatedDisk / $totalDisk) * 100, 1) : 0 }}%
                    </div>
                </div>
                <p style="margin:0;color:var(--vh-text-secondary);">
                    <strong id="disk-used">{{ number_format($allocatedDisk) }}</strong> MiB allocated of <strong>{{ number_format($totalDisk) }}</strong> MiB total
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Node Health Cards --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-heartbeat" style="margin-right:8px;color:var(--vh-accent,#6c5ce7);"></i>Node Health Status</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('admin.health') }}" class="btn btn-sm btn-primary"><i class="fa fa-external-link"></i> Full Health Monitor</a>
                </div>
            </div>
            <div class="box-body" id="node-health-cards">
                @if($nodeStats->isEmpty())
                    <p style="text-align:center;color:var(--vh-text-muted);padding:20px;">No nodes configured yet. <a href="{{ route('admin.nodes.new') }}">Add a node</a> to see health data.</p>
                @else
                    <div class="row">
                        @foreach($nodeStats as $node)
                        <div class="col-md-4 col-sm-6" style="margin-bottom:15px;">
                            <div style="background:var(--vh-surface,#12121a);border:1px solid var(--vh-border,#2a2a3a);border-radius:10px;padding:15px;position:relative;" id="node-card-{{ $node['id'] }}">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                                    <strong style="color:var(--vh-text,#e8e8f0);font-size:14px;">
                                        <i class="fa fa-sitemap" style="color:var(--vh-accent);margin-right:5px;"></i>
                                        {{ $node['name'] }}
                                    </strong>
                                    <span class="node-status-badge" id="node-badge-{{ $node['id'] }}" style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;
                                        @if($node['maintenance_mode'])
                                            background:rgba(255,165,2,0.2);color:#ffa502;">
                                            <i class="fa fa-wrench"></i> Maintenance
                                        @else
                                            background:rgba(46,213,115,0.2);color:#2ed573;">
                                            <i class="fa fa-circle"></i> Checking...
                                        @endif
                                    </span>
                                </div>
                                <div style="font-size:12px;color:var(--vh-text-muted);margin-bottom:8px;">
                                    {{ $node['fqdn'] }} &middot; {{ $node['location'] }} &middot; {{ $node['servers_count'] }} servers
                                </div>
                                {{-- Memory bar --}}
                                <div style="margin-bottom:6px;">
                                    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--vh-text-secondary);margin-bottom:3px;">
                                        <span>Memory</span>
                                        <span>{{ number_format($node['memory_used']) }} / {{ number_format($node['memory']) }} MiB ({{ $node['memory_percent'] }}%)</span>
                                    </div>
                                    <div style="background:var(--vh-surface-2,#1a1a28);border-radius:4px;overflow:hidden;height:6px;">
                                        <div style="height:100%;border-radius:4px;transition:width 0.6s ease;width:{{ $node['memory_percent'] }}%;background:{{ $node['memory_percent'] > 90 ? '#ff4757' : ($node['memory_percent'] > 70 ? '#ffa502' : '#6c5ce7') }};"></div>
                                    </div>
                                </div>
                                {{-- Disk bar --}}
                                <div>
                                    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--vh-text-secondary);margin-bottom:3px;">
                                        <span>Disk</span>
                                        <span>{{ number_format($node['disk_used']) }} / {{ number_format($node['disk']) }} MiB ({{ $node['disk_percent'] }}%)</span>
                                    </div>
                                    <div style="background:var(--vh-surface-2,#1a1a28);border-radius:4px;overflow:hidden;height:6px;">
                                        <div style="height:100%;border-radius:4px;transition:width 0.6s ease;width:{{ $node['disk_percent'] }}%;background:{{ $node['disk_percent'] > 90 ? '#ff4757' : ($node['disk_percent'] > 70 ? '#ffa502' : '#2ed573') }};"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions --}}
<div class="row" id="quick-actions">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bolt" style="margin-right:8px;color:var(--vh-accent,#6c5ce7);"></i>Quick Actions</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-6 col-sm-3 text-center" style="margin-bottom:10px;">
                        <a href="{{ route('admin.servers.new') }}"><button class="btn btn-primary" style="width:100%;"><i class="fa fa-fw fa-plus"></i> New Server</button></a>
                    </div>
                    <div class="col-xs-6 col-sm-3 text-center" style="margin-bottom:10px;">
                        <a href="{{ route('admin.users.new') }}"><button class="btn btn-success" style="width:100%;"><i class="fa fa-fw fa-user-plus"></i> New User</button></a>
                    </div>
                    <div class="col-xs-6 col-sm-3 text-center" style="margin-bottom:10px;">
                        <a href="{{ route('admin.nodes.new') }}"><button class="btn btn-warning" style="width:100%;"><i class="fa fa-fw fa-plus-circle"></i> New Node</button></a>
                    </div>
                    <div class="col-xs-6 col-sm-3 text-center" style="margin-bottom:10px;">
                        <a href="{{ route('admin.bulk-actions') }}"><button class="btn btn-default" style="width:100%;"><i class="fa fa-fw fa-tasks"></i> Bulk Actions</button></a>
                    </div>
                </div>
                <div class="row" style="margin-top:5px;">
                    <div class="col-xs-6 col-sm-3 text-center" style="margin-bottom:10px;">
                        <a href="{{ route('admin.trashbin') }}"><button class="btn btn-danger" style="width:100%;"><i class="fa fa-fw fa-trash"></i> Trash Bin ({{ $suspendedServers }})</button></a>
                    </div>
                    <div class="col-xs-6 col-sm-3 text-center" style="margin-bottom:10px;">
                        <a href="{{ route('admin.health') }}"><button class="btn btn-primary" style="width:100%;"><i class="fa fa-fw fa-heartbeat"></i> Health Monitor</button></a>
                    </div>
                    <div class="col-xs-6 col-sm-3 text-center" style="margin-bottom:10px;">
                        <a href="{{ route('admin.maintenance') }}"><button class="btn btn-warning" style="width:100%;"><i class="fa fa-fw fa-wrench"></i> Maintenance</button></a>
                    </div>
                    <div class="col-xs-6 col-sm-3 text-center" style="margin-bottom:10px;">
                        <a href="{{ route('admin.activity') }}"><button class="btn btn-default" style="width:100%;"><i class="fa fa-fw fa-history"></i> Activity Log</button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Activity --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-history" style="margin-right:8px;color:var(--vh-accent,#6c5ce7);"></i>Recent Activity</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('admin.activity') }}" class="btn btn-sm btn-default">View All</a>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Actor</th>
                            <th>IP Address</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentActivity as $activity)
                        <tr>
                            <td>
                                <code style="font-size:11px;">{{ $activity->event }}</code>
                            </td>
                            <td>
                                @if($activity->actor)
                                    {{ $activity->actor->username ?? $activity->actor->email ?? 'System' }}
                                @else
                                    <span style="color:var(--vh-text-muted);">System</span>
                                @endif
                            </td>
                            <td><code>{{ $activity->ip }}</code></td>
                            <td style="color:var(--vh-text-muted);font-size:12px;">{{ $activity->timestamp->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center;color:var(--vh-text-muted);padding:20px;">No recent activity</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Extra Info Row --}}
<div class="row">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-plug" style="margin-right:8px;color:var(--vh-accent);"></i>Allocations</h3>
            </div>
            <div class="box-body">
                <p><strong>{{ $allocationsUsed }}</strong> used of <strong>{{ $allocations }}</strong> total</p>
                <p style="color:var(--vh-success);">{{ $allocations - $allocationsUsed }} free</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-database" style="margin-right:8px;color:var(--vh-accent);"></i>Databases</h3>
            </div>
            <div class="box-body">
                <p><strong>{{ $databases }}</strong> databases configured</p>
                <a href="{{ route('admin.databases') }}">Manage Databases &raquo;</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-globe" style="margin-right:8px;color:var(--vh-accent);"></i>Locations</h3>
            </div>
            <div class="box-body">
                <p><strong>{{ $locations }}</strong> locations configured</p>
                <a href="{{ route('admin.locations') }}">Manage Locations &raquo;</a>
            </div>
        </div>
    </div>
</div>

{{-- Links & Resources --}}
<div class="row">
    <div class="col-xs-6 col-sm-3 text-center" style="margin-bottom:10px;">
        <a href="{{ $version->getDiscord() }}"><button class="btn btn-warning" style="width:100%;"><i class="fa fa-fw fa-support"></i> Get Help <small>(via Discord)</small></button></a>
    </div>
    <div class="col-xs-6 col-sm-3 text-center" style="margin-bottom:10px;">
        <a href="https://pterodactyl.io"><button class="btn btn-primary" style="width:100%;"><i class="fa fa-fw fa-link"></i> Documentation</button></a>
    </div>
    <div class="clearfix visible-xs-block">&nbsp;</div>
    <div class="col-xs-6 col-sm-3 text-center" style="margin-bottom:10px;">
        <a href="https://github.com/pterodactyl/panel"><button class="btn btn-primary" style="width:100%;"><i class="fa fa-fw fa-support"></i> GitHub</button></a>
    </div>
    <div class="col-xs-6 col-sm-3 text-center" style="margin-bottom:10px;">
        <a href="{{ $version->getDonations() }}"><button class="btn btn-success" style="width:100%;"><i class="fa fa-fw fa-money"></i> Support the Project</button></a>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    // Auto-refresh dashboard stats every 30 seconds
    function refreshDashboardStats() {
        $.getJSON('{{ route("admin.system-health.json") }}', function(data) {
            // Update stat cards
            $('#stat-servers').text(data.servers.total);
            $('#stat-nodes').text(data.nodes.total);
            $('#stat-users').text(data.users);
            $('#stat-active').text(data.servers.active);
            $('#stat-suspended').text(data.servers.suspended);
            $('#stat-installing').text(data.servers.installing);
            $('#stat-failed').text(data.servers.failed);

            // Update resource bars
            var memPct = data.resources.memory_percent;
            var diskPct = data.resources.disk_percent;
            $('#mem-bar').css('width', memPct + '%').text(memPct + '%');
            $('#mem-badge').text(memPct + '%');
            $('#mem-used').text(data.resources.memory_used.toLocaleString());
            $('#disk-bar').css('width', diskPct + '%').text(diskPct + '%');
            $('#disk-badge').text(diskPct + '%');
            $('#disk-used').text(data.resources.disk_used.toLocaleString());

            // Update node badges with online/offline status
            if (data.node_details) {
                data.node_details.forEach(function(node) {
                    var badge = $('#node-badge-' + node.id);
                    if (node.maintenance_mode) {
                        badge.html('<i class="fa fa-wrench"></i> Maintenance')
                             .css({'background': 'rgba(255,165,2,0.2)', 'color': '#ffa502'});
                    } else if (node.online) {
                        badge.html('<i class="fa fa-circle"></i> Online')
                             .css({'background': 'rgba(46,213,115,0.2)', 'color': '#2ed573'});
                    } else {
                        badge.html('<i class="fa fa-circle"></i> Offline')
                             .css({'background': 'rgba(255,71,87,0.2)', 'color': '#ff4757'});
                    }
                });
            }
        });
    }

    // Initial load + interval
    $(document).ready(function() {
        refreshDashboardStats();
        setInterval(refreshDashboardStats, 30000);
    });
    </script>
@endsection
