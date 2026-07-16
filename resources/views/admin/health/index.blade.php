@extends('layouts.admin')

@section('title')
    System Health Monitor
@endsection

@section('content-header')
    <h1>System Health Monitor<small>Real-time infrastructure status</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Health Monitor</li>
    </ol>
@endsection

@section('content')

{{-- Global Overview Cards --}}
<div class="row">
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <div class="box" style="text-align:center;padding:15px 10px;">
            <h3 style="margin:0;color:var(--vh-accent);">{{ $totalServers }}</h3>
            <small style="color:var(--vh-text-secondary);">Total Servers</small>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <div class="box" style="text-align:center;padding:15px 10px;">
            <h3 style="margin:0;color:var(--vh-success);">{{ $activeServers }}</h3>
            <small style="color:var(--vh-text-secondary);">Active</small>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <div class="box" style="text-align:center;padding:15px 10px;">
            <h3 style="margin:0;color:var(--vh-danger);">{{ $suspendedServers }}</h3>
            <small style="color:var(--vh-text-secondary);">Suspended</small>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <div class="box" style="text-align:center;padding:15px 10px;">
            <h3 style="margin:0;color:var(--vh-warning);">{{ $installingServers }}</h3>
            <small style="color:var(--vh-text-secondary);">Installing</small>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <div class="box" style="text-align:center;padding:15px 10px;">
            <h3 style="margin:0;color:#ff6348;">{{ $failedServers }}</h3>
            <small style="color:var(--vh-text-secondary);">Failed</small>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <div class="box" style="text-align:center;padding:15px 10px;">
            <h3 style="margin:0;color:var(--vh-warning);">{{ $nodesInMaintenance }}</h3>
            <small style="color:var(--vh-text-secondary);">In Maintenance</small>
        </div>
    </div>
</div>

{{-- Resource Summary --}}
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-microchip" style="margin-right:8px;color:var(--vh-accent);"></i>Global Memory Usage</h3>
            </div>
            <div class="box-body">
                @php
                    $memPct = $totalMemory > 0 ? round(($allocatedMemory / $totalMemory) * 100, 1) : 0;
                    $memColor = $memPct > 90 ? '#ff4757' : ($memPct > 70 ? '#ffa502' : '#6c5ce7');
                @endphp
                <div style="background:var(--vh-surface);border-radius:8px;overflow:hidden;height:32px;margin-bottom:10px;">
                    <div style="height:100%;background:{{ $memColor }};border-radius:8px;width:{{ $memPct }}%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:13px;transition:width 0.6s ease;">
                        {{ $memPct }}%
                    </div>
                </div>
                <p style="color:var(--vh-text-secondary);">
                    <strong>{{ number_format($allocatedMemory) }}</strong> MiB allocated of <strong>{{ number_format($totalMemory) }}</strong> MiB total capacity
                </p>
                <p style="color:var(--vh-text-muted);font-size:12px;">
                    {{ number_format($totalMemory - $allocatedMemory) }} MiB free across {{ $totalNodes }} node(s)
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-hdd-o" style="margin-right:8px;color:var(--vh-accent);"></i>Global Disk Usage</h3>
            </div>
            <div class="box-body">
                @php
                    $diskPct = $totalDisk > 0 ? round(($allocatedDisk / $totalDisk) * 100, 1) : 0;
                    $diskColor = $diskPct > 90 ? '#ff4757' : ($diskPct > 70 ? '#ffa502' : '#2ed573');
                @endphp
                <div style="background:var(--vh-surface);border-radius:8px;overflow:hidden;height:32px;margin-bottom:10px;">
                    <div style="height:100%;background:{{ $diskColor }};border-radius:8px;width:{{ $diskPct }}%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:13px;transition:width 0.6s ease;">
                        {{ $diskPct }}%
                    </div>
                </div>
                <p style="color:var(--vh-text-secondary);">
                    <strong>{{ number_format($allocatedDisk) }}</strong> MiB allocated of <strong>{{ number_format($totalDisk) }}</strong> MiB total capacity
                </p>
                <p style="color:var(--vh-text-muted);font-size:12px;">
                    {{ number_format($totalDisk - $allocatedDisk) }} MiB free across {{ $totalNodes }} node(s)
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Infrastructure Stats --}}
<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="box" style="text-align:center;padding:20px;">
            <i class="fa fa-sitemap" style="font-size:28px;color:var(--vh-accent);margin-bottom:8px;"></i>
            <h4 style="margin:5px 0;">{{ $totalNodes }} Nodes</h4>
            <small style="color:var(--vh-text-muted);">{{ $nodesInMaintenance }} in maintenance</small>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="box" style="text-align:center;padding:20px;">
            <i class="fa fa-plug" style="font-size:28px;color:var(--vh-success);margin-bottom:8px;"></i>
            <h4 style="margin:5px 0;">{{ $totalAllocations }} Allocations</h4>
            <small style="color:var(--vh-text-muted);">{{ $usedAllocations }} used / {{ $totalAllocations - $usedAllocations }} free</small>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="box" style="text-align:center;padding:20px;">
            <i class="fa fa-database" style="font-size:28px;color:var(--vh-warning);margin-bottom:8px;"></i>
            <h4 style="margin:5px 0;">{{ $totalDatabases }} Databases</h4>
            <small style="color:var(--vh-text-muted);">{{ $totalEggs }} eggs configured</small>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="box" style="text-align:center;padding:20px;">
            <i class="fa fa-globe" style="font-size:28px;color:var(--vh-danger);margin-bottom:8px;"></i>
            <h4 style="margin:5px 0;">{{ $totalLocations }} Locations</h4>
            <small style="color:var(--vh-text-muted);">{{ $totalUsers }} registered users</small>
        </div>
    </div>
</div>

{{-- Per-Node Health Details --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-server" style="margin-right:8px;color:var(--vh-accent);"></i>Node Details &amp; Daemon Status</h3>
                <div class="box-tools pull-right">
                    <button class="btn btn-sm btn-primary" onclick="checkAllNodes();" id="check-all-btn"><i class="fa fa-refresh"></i> Check All Nodes</button>
                </div>
            </div>
            <div class="box-body">
                @if($nodeData->isEmpty())
                    <p style="text-align:center;color:var(--vh-text-muted);padding:30px;">No nodes configured. <a href="{{ route('admin.nodes.new') }}">Add your first node</a>.</p>
                @else
                    <div class="row">
                        @foreach($nodeData as $node)
                        <div class="col-md-6 col-lg-4" style="margin-bottom:20px;">
                            <div style="background:var(--vh-surface);border:1px solid var(--vh-border);border-radius:12px;padding:20px;height:100%;" id="health-node-{{ $node['id'] }}">
                                {{-- Header --}}
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                                    <div>
                                        <strong style="font-size:15px;color:var(--vh-text);">{{ $node['name'] }}</strong>
                                        <div style="font-size:11px;color:var(--vh-text-muted);margin-top:2px;">{{ $node['fqdn'] }}</div>
                                    </div>
                                    <span class="node-live-badge" id="health-badge-{{ $node['id'] }}" style="padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;background:rgba(108,92,231,0.2);color:var(--vh-accent);">
                                        <i class="fa fa-spinner fa-spin"></i> Checking
                                    </span>
                                </div>

                                {{-- Info --}}
                                <div style="display:flex;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                                    <span style="font-size:11px;color:var(--vh-text-muted);"><i class="fa fa-map-marker"></i> {{ $node['location'] }}</span>
                                    <span style="font-size:11px;color:var(--vh-text-muted);"><i class="fa fa-server"></i> {{ $node['servers_total'] }} servers</span>
                                    <span style="font-size:11px;color:var(--vh-success);"><i class="fa fa-check"></i> {{ $node['servers_active'] }} active</span>
                                    @if($node['servers_suspended'] > 0)
                                    <span style="font-size:11px;color:var(--vh-danger);"><i class="fa fa-ban"></i> {{ $node['servers_suspended'] }} suspended</span>
                                    @endif
                                </div>

                                @if($node['maintenance_mode'])
                                <div style="background:rgba(255,165,2,0.1);border:1px solid rgba(255,165,2,0.3);border-radius:8px;padding:8px 12px;margin-bottom:12px;font-size:12px;color:#ffa502;">
                                    <i class="fa fa-wrench"></i> This node is in maintenance mode
                                </div>
                                @endif

                                {{-- Memory --}}
                                <div style="margin-bottom:10px;">
                                    <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--vh-text-secondary);margin-bottom:4px;">
                                        <span><i class="fa fa-microchip"></i> Memory</span>
                                        <span>{{ number_format($node['memory_used']) }} / {{ number_format($node['memory_total']) }} MiB</span>
                                    </div>
                                    <div style="background:var(--vh-surface-2);border-radius:6px;overflow:hidden;height:10px;">
                                        @php
                                            $mColor = $node['memory_percent'] > 90 ? '#ff4757' : ($node['memory_percent'] > 70 ? '#ffa502' : '#6c5ce7');
                                        @endphp
                                        <div style="height:100%;border-radius:6px;width:{{ $node['memory_percent'] }}%;background:{{ $mColor }};transition:width 0.6s ease;"></div>
                                    </div>
                                    <div style="text-align:right;font-size:11px;color:var(--vh-text-muted);margin-top:2px;">{{ $node['memory_percent'] }}% (overalloc: {{ $node['memory_overallocate'] }}%)</div>
                                </div>

                                {{-- Disk --}}
                                <div style="margin-bottom:12px;">
                                    <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--vh-text-secondary);margin-bottom:4px;">
                                        <span><i class="fa fa-hdd-o"></i> Disk</span>
                                        <span>{{ number_format($node['disk_used']) }} / {{ number_format($node['disk_total']) }} MiB</span>
                                    </div>
                                    <div style="background:var(--vh-surface-2);border-radius:6px;overflow:hidden;height:10px;">
                                        @php
                                            $dColor = $node['disk_percent'] > 90 ? '#ff4757' : ($node['disk_percent'] > 70 ? '#ffa502' : '#2ed573');
                                        @endphp
                                        <div style="height:100%;border-radius:6px;width:{{ $node['disk_percent'] }}%;background:{{ $dColor }};transition:width 0.6s ease;"></div>
                                    </div>
                                    <div style="text-align:right;font-size:11px;color:var(--vh-text-muted);margin-top:2px;">{{ $node['disk_percent'] }}% (overalloc: {{ $node['disk_overallocate'] }}%)</div>
                                </div>

                                {{-- Daemon Info (filled by JS) --}}
                                <div id="daemon-info-{{ $node['id'] }}" style="font-size:11px;color:var(--vh-text-muted);border-top:1px solid var(--vh-border);padding-top:10px;display:none;">
                                </div>

                                {{-- Actions --}}
                                <div style="display:flex;gap:8px;margin-top:12px;">
                                    <a href="{{ route('admin.nodes.view', $node['id']) }}" class="btn btn-xs btn-default"><i class="fa fa-cog"></i> Manage</a>
                                    <button class="btn btn-xs btn-primary" onclick="checkNodeDaemon({{ $node['id'] }})"><i class="fa fa-refresh"></i> Check</button>
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
@endsection

@section('footer-scripts')
    @parent
    <script>
    function checkNodeDaemon(nodeId) {
        var badge = $('#health-badge-' + nodeId);
        var info = $('#daemon-info-' + nodeId);
        badge.html('<i class="fa fa-spinner fa-spin"></i> Checking').css({'background':'rgba(108,92,231,0.2)','color':'var(--vh-accent)'});

        $.getJSON('/admin/health/check-node/' + nodeId, function(data) {
            if (data.online) {
                badge.html('<i class="fa fa-circle"></i> Online').css({'background':'rgba(46,213,115,0.2)','color':'#2ed573'});
                info.show().html(
                    '<i class="fa fa-code-fork"></i> Wings ' + data.version +
                    ' &middot; <i class="fa fa-linux"></i> ' + data.os +
                    ' &middot; ' + data.architecture +
                    ' &middot; ' + data.cpu_count + ' CPUs'
                );
            } else {
                badge.html('<i class="fa fa-circle"></i> Offline').css({'background':'rgba(255,71,87,0.2)','color':'#ff4757'});
                info.show().html('<i class="fa fa-exclamation-triangle"></i> ' + (data.error || 'Connection failed'));
            }
        }).fail(function() {
            badge.html('<i class="fa fa-circle"></i> Error').css({'background':'rgba(255,71,87,0.2)','color':'#ff4757'});
            info.show().html('<i class="fa fa-exclamation-triangle"></i> Failed to reach panel API');
        });
    }

    function checkAllNodes() {
        var btn = $('#check-all-btn');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Checking...');

        var nodeIds = [];
        $('[id^="health-node-"]').each(function() {
            var id = $(this).attr('id').replace('health-node-', '');
            nodeIds.push(parseInt(id));
        });

        var checked = 0;
        nodeIds.forEach(function(id) {
            checkNodeDaemon(id);
            checked++;
            if (checked === nodeIds.length) {
                setTimeout(function() {
                    btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Check All Nodes');
                }, 3000);
            }
        });

        if (nodeIds.length === 0) {
            btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Check All Nodes');
        }
    }

    // Auto-check all nodes on page load
    $(document).ready(function() {
        setTimeout(checkAllNodes, 500);
    });
    </script>
@endsection
