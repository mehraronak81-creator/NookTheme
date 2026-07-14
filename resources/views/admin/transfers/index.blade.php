@extends('layouts.admin')

@section('title')
    Server Transfers
@endsection

@section('content-header')
    <h1>Server Transfer Queue<small>Monitor and manage server transfers between nodes</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Server Transfers</li>
    </ol>
@endsection

@section('content')
{{-- Stats --}}
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#7382FF,#a78bfa);border-radius:12px;">
            <div class="inner">
                <h3>{{ $totalTransfers }}</h3>
                <p>Total Transfers</p>
            </div>
            <div class="icon"><i class="fa fa-exchange"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#ffa502,#ff7f50);border-radius:12px;">
            <div class="inner">
                <h3>{{ $inProgressTransfers }}</h3>
                <p>In Progress</p>
            </div>
            <div class="icon"><i class="fa fa-spinner"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#2ed573,#17c964);border-radius:12px;">
            <div class="inner">
                <h3>{{ $successfulTransfers }}</h3>
                <p>Successful</p>
            </div>
            <div class="icon"><i class="fa fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:linear-gradient(135deg,#ff4757,#ff6b81);border-radius:12px;">
            <div class="inner">
                <h3>{{ $failedTransfers }}</h3>
                <p>Failed</p>
            </div>
            <div class="icon"><i class="fa fa-times-circle"></i></div>
        </div>
    </div>
</div>

{{-- Pending Transfers --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clock-o" style="margin-right:8px;color:var(--vh-warning);"></i>Pending / In-Progress Transfers ({{ $pendingTransfers->count() }})</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                @if($pendingTransfers->isEmpty())
                    <p style="text-align:center;color:var(--vh-text-muted);padding:30px;">No pending transfers. All clear!</p>
                @else
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Server</th>
                                <th>Owner</th>
                                <th>From Node</th>
                                <th>To Node</th>
                                <th>Resources</th>
                                <th>Started</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingTransfers as $transfer)
                            <tr>
                                <td>
                                    @if($transfer->server)
                                        <a href="{{ route('admin.servers.view', $transfer->server->id) }}">
                                            {{ $transfer->server->name }}
                                        </a>
                                        <br><small style="color:var(--vh-text-muted);">{{ $transfer->server->uuid_short }}</small>
                                    @else
                                        <span style="color:var(--vh-text-muted);">Deleted</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transfer->server && $transfer->server->user)
                                        {{ $transfer->server->user->username }}
                                    @else
                                        <span style="color:var(--vh-text-muted);">-</span>
                                    @endif
                                </td>
                                <td>{{ $transfer->oldNode->name ?? 'Unknown' }}</td>
                                <td>{{ $transfer->newNode->name ?? 'Unknown' }}</td>
                                <td>
                                    @if($transfer->server)
                                        <small>{{ number_format($transfer->server->memory) }} MiB / {{ number_format($transfer->server->disk) }} MiB</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="color:var(--vh-text-muted);font-size:12px;">{{ $transfer->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Node Capacity Overview --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-balance-scale" style="margin-right:8px;color:var(--vh-accent);"></i>Node Capacity Overview</h3>
                <small class="pull-right" style="color:var(--vh-text-muted);margin-top:4px;">Sorted by memory usage (lowest first)</small>
            </div>
            <div class="box-body">
                @if($nodeCapacity->isEmpty())
                    <p style="text-align:center;color:var(--vh-text-muted);padding:20px;">No nodes configured.</p>
                @else
                    <div class="row">
                        @foreach($nodeCapacity as $node)
                        <div class="col-md-4 col-sm-6" style="margin-bottom:15px;">
                            <div style="background:var(--vh-surface,#12121a);border:1px solid var(--vh-border,#2a2a3a);border-radius:10px;padding:15px;{{ $node['maintenance'] ? 'opacity:0.6;' : '' }}">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                                    <strong style="color:var(--vh-text);font-size:14px;">
                                        <i class="fa fa-sitemap" style="color:var(--vh-accent);margin-right:5px;"></i>
                                        {{ $node['name'] }}
                                    </strong>
                                    <span style="font-size:11px;color:var(--vh-text-muted);">{{ $node['servers'] }} servers</span>
                                </div>
                                <div style="font-size:12px;color:var(--vh-text-muted);margin-bottom:10px;">
                                    {{ $node['fqdn'] }} &middot; {{ $node['location'] }}
                                    @if($node['maintenance'])
                                        &middot; <span style="color:var(--vh-warning);"><i class="fa fa-wrench"></i> Maintenance</span>
                                    @endif
                                </div>
                                {{-- Memory --}}
                                <div style="margin-bottom:8px;">
                                    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--vh-text-secondary);margin-bottom:3px;">
                                        <span>Memory</span>
                                        <span>{{ number_format($node['memory_used']) }} / {{ number_format($node['memory_total']) }} MiB ({{ $node['memory_percent'] }}%)</span>
                                    </div>
                                    <div style="background:var(--vh-surface-2,#1a1a28);border-radius:4px;overflow:hidden;height:6px;">
                                        <div style="height:100%;border-radius:4px;width:{{ $node['memory_percent'] }}%;background:{{ $node['memory_percent'] > 90 ? 'var(--vh-danger)' : ($node['memory_percent'] > 70 ? 'var(--vh-warning)' : 'var(--vh-accent)') }};transition:width 0.3s ease;"></div>
                                    </div>
                                    <div style="font-size:10px;color:var(--vh-success);margin-top:2px;">{{ number_format($node['memory_free']) }} MiB free</div>
                                </div>
                                {{-- Disk --}}
                                <div>
                                    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--vh-text-secondary);margin-bottom:3px;">
                                        <span>Disk</span>
                                        <span>{{ number_format($node['disk_used']) }} / {{ number_format($node['disk_total']) }} MiB ({{ $node['disk_percent'] }}%)</span>
                                    </div>
                                    <div style="background:var(--vh-surface-2,#1a1a28);border-radius:4px;overflow:hidden;height:6px;">
                                        <div style="height:100%;border-radius:4px;width:{{ $node['disk_percent'] }}%;background:{{ $node['disk_percent'] > 90 ? 'var(--vh-danger)' : ($node['disk_percent'] > 70 ? 'var(--vh-warning)' : '#2ed573') }};transition:width 0.3s ease;"></div>
                                    </div>
                                    <div style="font-size:10px;color:var(--vh-success);margin-top:2px;">{{ number_format($node['disk_free']) }} MiB free</div>
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

{{-- Recent Completed Transfers --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-history" style="margin-right:8px;color:var(--vh-accent);"></i>Recent Completed Transfers</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                @if($completedTransfers->isEmpty())
                    <p style="text-align:center;color:var(--vh-text-muted);padding:30px;">No completed transfers yet.</p>
                @else
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Server</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completedTransfers as $transfer)
                            <tr>
                                <td>
                                    @if($transfer->successful)
                                        <span style="color:var(--vh-success);"><i class="fa fa-check-circle"></i> Success</span>
                                    @else
                                        <span style="color:var(--vh-danger);"><i class="fa fa-times-circle"></i> Failed</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transfer->server)
                                        <a href="{{ route('admin.servers.view', $transfer->server->id) }}">{{ $transfer->server->name }}</a>
                                    @else
                                        <span style="color:var(--vh-text-muted);">Deleted</span>
                                    @endif
                                </td>
                                <td>{{ $transfer->oldNode->name ?? 'Unknown' }}</td>
                                <td>{{ $transfer->newNode->name ?? 'Unknown' }}</td>
                                <td style="color:var(--vh-text-muted);font-size:12px;">{{ $transfer->updated_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
