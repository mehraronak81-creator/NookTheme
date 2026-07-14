@extends('layouts.admin')

@section('title')
    Resource Monitor
@endsection

@section('content-header')
    <h1>Resource Monitor<small>Real-time node resource tracking</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Resources</li>
    </ol>
@endsection

@section('content')
{{-- Global Summary --}}
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#6c5ce7,#a855f7);border-radius:12px;">
            <div class="inner">
                <h3>{{ number_format($usedMem) }} <small style="font-size:14px;">/ {{ number_format($totalMem) }} MB</small></h3>
                <p>Memory Allocated</p>
            </div>
            <div class="icon"><i class="fa fa-microchip"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#2ed573,#17c964);border-radius:12px;">
            <div class="inner">
                <h3>{{ number_format($usedDisk) }} <small style="font-size:14px;">/ {{ number_format($totalDisk) }} MB</small></h3>
                <p>Disk Allocated</p>
            </div>
            <div class="icon"><i class="fa fa-hdd-o"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:{{ $alerts->count() > 0 ? 'linear-gradient(135deg,#ff4757,#ff6b81)' : 'linear-gradient(135deg,#3742fa,#5352ed)' }};border-radius:12px;">
            <div class="inner">
                <h3>{{ $alerts->count() }}</h3>
                <p>Resource Alerts</p>
            </div>
            <div class="icon"><i class="fa fa-{{ $alerts->count() > 0 ? 'exclamation-triangle' : 'check' }}"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#ffa502,#ff9f43);border-radius:12px;">
            <div class="inner">
                <h3>{{ $nodeResources->count() }}</h3>
                <p>Total Nodes</p>
            </div>
            <div class="icon"><i class="fa fa-sitemap"></i></div>
        </div>
    </div>
</div>

{{-- Alerts --}}
@if($alerts->count() > 0)
<div class="row">
    <div class="col-xs-12">
        <div class="alert" style="background:rgba(255,71,87,0.15);border:1px solid var(--vh-danger);color:var(--vh-danger);border-radius:10px;">
            <i class="fa fa-exclamation-triangle" style="margin-right:8px;"></i>
            <strong>Warning:</strong> {{ $alerts->count() }} node(s) are above 85% resource utilization!
            @foreach($alerts as $a)
                <strong>{{ $a['name'] }}</strong> (Mem: {{ $a['memory_percent'] }}%, Disk: {{ $a['disk_percent'] }}%){{ !$loop->last ? ',' : '' }}
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Per-Node Resource Cards --}}
<div class="row" id="node-resources">
    @foreach($nodeResources as $node)
    <div class="col-md-6 col-lg-4">
        <div class="box {{ $node['maintenance'] ? 'box-warning' : ($node['memory_percent'] > 85 || $node['disk_percent'] > 85 ? 'box-danger' : 'box-primary') }}">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-server" style="margin-right:6px;"></i>{{ $node['name'] }}
                    @if($node['maintenance'])
                        <span class="label" style="background:var(--vh-warning);font-size:10px;">MAINTENANCE</span>
                    @endif
                </h3>
                <span class="pull-right" style="color:var(--vh-text-muted);font-size:12px;">{{ $node['location'] }} &middot; {{ $node['servers_count'] }} servers</span>
            </div>
            <div class="box-body">
                <p style="color:var(--vh-text-secondary);margin-bottom:4px;">
                    <i class="fa fa-microchip" style="width:16px;"></i> Memory: <strong>{{ number_format($node['memory_used']) }}</strong> / {{ number_format($node['memory_max']) }} MB
                </p>
                <div class="progress progress-sm" style="margin-bottom:12px;">
                    <div class="progress-bar" style="width:{{ $node['memory_percent'] }}%;background:{{ $node['memory_percent'] > 85 ? 'var(--vh-danger)' : ($node['memory_percent'] > 60 ? 'var(--vh-warning)' : 'var(--vh-accent)') }};"></div>
                </div>

                <p style="color:var(--vh-text-secondary);margin-bottom:4px;">
                    <i class="fa fa-hdd-o" style="width:16px;"></i> Disk: <strong>{{ number_format($node['disk_used']) }}</strong> / {{ number_format($node['disk_max']) }} MB
                </p>
                <div class="progress progress-sm" style="margin-bottom:12px;">
                    <div class="progress-bar" style="width:{{ $node['disk_percent'] }}%;background:{{ $node['disk_percent'] > 85 ? 'var(--vh-danger)' : ($node['disk_percent'] > 60 ? 'var(--vh-warning)' : 'var(--vh-success)') }};"></div>
                </div>

                <p style="color:var(--vh-text-secondary);margin-bottom:4px;">
                    <i class="fa fa-plug" style="width:16px;"></i> Allocations: <strong>{{ $node['alloc_used'] }}</strong> / {{ $node['alloc_total'] }}
                </p>
                <div class="progress progress-sm" style="margin-bottom:4px;">
                    <div class="progress-bar" style="width:{{ $node['alloc_percent'] }}%;background:var(--vh-accent);"></div>
                </div>

                <small style="color:var(--vh-text-muted);">Overalloc: Mem {{ $node['overalloc_mem'] }}% | Disk {{ $node['overalloc_disk'] }}%</small>
            </div>
            <div class="box-footer" style="text-align:right;">
                <a href="{{ route('admin.nodes.view', $node['id']) }}" class="btn btn-xs btn-default"><i class="fa fa-eye"></i> View Node</a>
            </div>
        </div>
    </div>
    @endforeach
</div>

<script>
// Auto-refresh every 30s
setInterval(function() {
    $.get('{{ route('admin.resources.live') }}', function(data) {
        // Update would require DOM manipulation - for now page refresh
        console.log('Resource data refreshed', data);
    });
}, 30000);
</script>
@endsection
