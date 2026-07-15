@extends('layouts.admin')

@section('title')
    Server Analytics
@endsection

@section('content-header')
    <h1>Server Analytics<small>Insights & resource distribution</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Analytics</li>
    </ol>
@endsection

@section('content')
{{-- Resource Summary --}}
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#6c5ce7,#a855f7);border-radius:12px;">
            <div class="inner">
                <h3>{{ $statusBreakdown['active'] }}</h3>
                <p>Active Servers</p>
            </div>
            <div class="icon"><i class="fa fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#ff4757,#ff6b81);border-radius:12px;">
            <div class="inner">
                <h3>{{ $statusBreakdown['suspended'] }}</h3>
                <p>Suspended</p>
            </div>
            <div class="icon"><i class="fa fa-pause-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#3742fa,#5352ed);border-radius:12px;">
            <div class="inner">
                <h3>{{ round($totalAllocatedMem / max($totalAvailMem, 1) * 100, 1) }}%</h3>
                <p>Memory Usage</p>
            </div>
            <div class="icon"><i class="fa fa-microchip"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#2ed573,#17c964);border-radius:12px;">
            <div class="inner">
                <h3>{{ round($totalAllocatedDisk / max($totalAvailDisk, 1) * 100, 1) }}%</h3>
                <p>Disk Usage</p>
            </div>
            <div class="icon"><i class="fa fa-hdd-o"></i></div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Server Distribution Chart --}}
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-pie-chart" style="margin-right:8px;color:var(--vh-accent);"></i>Server Distribution by Node</h3>
            </div>
            <div class="box-body">
                <canvas id="nodeDistChart" height="260"></canvas>
            </div>
        </div>
    </div>

    {{-- Status Breakdown --}}
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bar-chart" style="margin-right:8px;color:var(--vh-accent);"></i>Server Status Breakdown</h3>
            </div>
            <div class="box-body">
                <canvas id="statusChart" height="260"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Top Memory Consumers --}}
    <div class="col-md-6">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-sort-amount-desc" style="margin-right:8px;"></i>Top Memory Consumers</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead><tr><th>Server</th><th>Owner</th><th>Node</th><th>Memory</th></tr></thead>
                    <tbody>
                    @foreach($topMemoryServers as $srv)
                        <tr>
                            <td><a href="{{ route('admin.servers.view', $srv->id) }}">{{ $srv->name }}</a></td>
                            <td>{{ $srv->user->username ?? '-' }}</td>
                            <td>{{ $srv->node->name ?? '-' }}</td>
                            <td><strong>{{ number_format($srv->memory) }} MB</strong></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top Disk Consumers --}}
    <div class="col-md-6">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-sort-amount-desc" style="margin-right:8px;"></i>Top Disk Consumers</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead><tr><th>Server</th><th>Owner</th><th>Node</th><th>Disk</th></tr></thead>
                    <tbody>
                    @foreach($topDiskServers as $srv)
                        <tr>
                            <td><a href="{{ route('admin.servers.view', $srv->id) }}">{{ $srv->name }}</a></td>
                            <td>{{ $srv->user->username ?? '-' }}</td>
                            <td>{{ $srv->node->name ?? '-' }}</td>
                            <td><strong>{{ number_format($srv->disk) }} MB</strong></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Users by Server Count --}}
    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-users" style="margin-right:8px;color:var(--vh-accent);"></i>Top Users by Server Count</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead><tr><th>User</th><th>Email</th><th>Servers</th></tr></thead>
                    <tbody>
                    @foreach($userServerCounts as $u)
                        <tr>
                            <td><a href="{{ route('admin.users.view', $u->id) }}">{{ $u->username }}</a></td>
                            <td>{{ $u->email }}</td>
                            <td><span class="label" style="background:var(--vh-accent);">{{ $u->servers_count }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Egg Popularity --}}
    <div class="col-md-6">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-th-large" style="margin-right:8px;color:var(--vh-success);"></i>Most Popular Eggs</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead><tr><th>Egg</th><th>Servers</th><th>Share</th></tr></thead>
                    <tbody>
                    @php $totalSrv = array_sum($statusBreakdown); @endphp
                    @foreach($eggPopularity as $egg)
                        <tr>
                            <td>{{ $egg->egg->name ?? 'Unknown' }}</td>
                            <td>{{ $egg->count }}</td>
                            <td>
                                <div class="progress progress-xs" style="margin:0;">
                                    <div class="progress-bar" style="width:{{ $totalSrv > 0 ? round($egg->count / $totalSrv * 100) : 0 }}%;background:var(--vh-accent);"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Node distribution
    new Chart(document.getElementById('nodeDistChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($nodeDistribution->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($nodeDistribution->pluck('servers_count')) !!},
                backgroundColor: ['#6c5ce7','#2ed573','#ff4757','#3742fa','#ffa502','#a855f7','#17c964','#ff6b81']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { color: '#b0b0c0' } } }
        }
    });

    // Status chart
    new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: {
            labels: ['Active', 'Suspended', 'Installing', 'Failed'],
            datasets: [{
                label: 'Servers',
                data: [{{ $statusBreakdown['active'] }}, {{ $statusBreakdown['suspended'] }}, {{ $statusBreakdown['installing'] }}, {{ $statusBreakdown['failed'] }}],
                backgroundColor: ['#2ed573','#ff4757','#ffa502','#ff6b81']
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { ticks: { color: '#b0b0c0' }, grid: { color: '#2a2a3a' } },
                y: { ticks: { color: '#b0b0c0' }, grid: { color: '#2a2a3a' } }
            },
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endsection
