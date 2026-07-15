@extends('layouts.admin')

@section('title')
    System Environment
@endsection

@section('content-header')
    <h1>System Environment<small>Diagnostics &amp; health checks for your VantaHost installation</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">System Environment</li>
    </ol>
@endsection

@section('content')
{{-- Overall Status --}}
@php
    $allExtOk = !in_array(false, $extensionStatus, true);
    $allDirOk = collect($dirStatus)->every(fn($d) => $d['exists'] && $d['writable']);
    $overallOk = $phpOk && $allExtOk && $allDirOk && $dbOk && $cacheOk;
@endphp
<div class="row">
    <div class="col-xs-12">
        <div class="box {{ $overallOk ? 'box-success' : 'box-danger' }}">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa {{ $overallOk ? 'fa-check-circle' : 'fa-exclamation-triangle' }}" style="margin-right:8px;color:{{ $overallOk ? 'var(--vh-success)' : 'var(--vh-danger)' }};"></i>
                    Overall Status: {{ $overallOk ? 'All Systems Operational' : 'Issues Detected' }}
                </h3>
            </div>
        </div>
    </div>
</div>

{{-- Application Config --}}
<div class="row">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-cog" style="margin-right:8px;color:var(--vh-accent);"></i>Application</h3>
            </div>
            <div class="box-body">
                <table class="table table-condensed" style="margin:0;">
                    <tr><td>Environment</td><td><code>{{ $appEnv }}</code></td></tr>
                    <tr><td>Debug Mode</td><td>
                        @if($appDebug)
                            <span style="color:var(--vh-warning);"><i class="fa fa-exclamation-triangle"></i> Enabled</span>
                        @else
                            <span style="color:var(--vh-success);"><i class="fa fa-check"></i> Disabled</span>
                        @endif
                    </td></tr>
                    <tr><td>App URL</td><td><code style="font-size:11px;">{{ $appUrl }}</code></td></tr>
                    <tr><td>Cache Driver</td><td><code>{{ $cacheDriver }}</code> {!! $cacheOk ? '<i class="fa fa-check" style="color:var(--vh-success);"></i>' : '<i class="fa fa-times" style="color:var(--vh-danger);"></i>' !!}</td></tr>
                    <tr><td>Queue Driver</td><td><code>{{ $queueDriver }}</code></td></tr>
                    <tr><td>Session Driver</td><td><code>{{ $sessionDriver }}</code></td></tr>
                    <tr><td>Mail Driver</td><td><code>{{ $mailDriver }}</code></td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- PHP Info --}}
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-code" style="margin-right:8px;color:var(--vh-accent);"></i>PHP Runtime</h3>
            </div>
            <div class="box-body">
                <table class="table table-condensed" style="margin:0;">
                    <tr><td>PHP Version</td><td>
                        <code>{{ $phpVersion }}</code>
                        @if($phpOk)
                            <i class="fa fa-check" style="color:var(--vh-success);margin-left:6px;"></i>
                        @else
                            <i class="fa fa-times" style="color:var(--vh-danger);margin-left:6px;"></i> <small>Requires {{ $phpMinimum }}+</small>
                        @endif
                    </td></tr>
                    <tr><td>Memory Limit</td><td><code>{{ $memoryLimit }}</code></td></tr>
                    <tr><td>Max Execution Time</td><td><code>{{ $maxExecTime }}s</code></td></tr>
                    <tr><td>Upload Max Size</td><td><code>{{ $uploadMax }}</code></td></tr>
                    <tr><td>Post Max Size</td><td><code>{{ $postMax }}</code></td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Database & Disk --}}
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-database" style="margin-right:8px;color:var(--vh-accent);"></i>Database & Disk</h3>
            </div>
            <div class="box-body">
                <table class="table table-condensed" style="margin:0;">
                    <tr><td>DB Driver</td><td><code>{{ $dbDriver }}</code> {!! $dbOk ? '<i class="fa fa-check" style="color:var(--vh-success);"></i>' : '<i class="fa fa-times" style="color:var(--vh-danger);"></i>' !!}</td></tr>
                    <tr><td>DB Version</td><td><code style="font-size:11px;">{{ \Illuminate\Support\Str::limit($dbVersion, 30) }}</code></td></tr>
                    <tr><td>Disk Free</td><td><code>{{ number_format($diskFree / 1073741824, 1) }} GB</code> of {{ number_format($diskTotal / 1073741824, 1) }} GB</td></tr>
                    <tr><td>Disk Usage</td><td>
                        <div style="background:var(--vh-surface,#12121a);border-radius:4px;overflow:hidden;height:8px;margin-top:4px;">
                            <div style="height:100%;border-radius:4px;width:{{ 100 - $diskPercent }}%;background:{{ (100 - $diskPercent) > 90 ? 'var(--vh-danger)' : 'var(--vh-accent)' }};"></div>
                        </div>
                    </td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- PHP Extensions --}}
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-puzzle-piece" style="margin-right:8px;color:var(--vh-accent);"></i>Required PHP Extensions</h3>
                <span class="pull-right label" style="background:{{ $allExtOk ? 'var(--vh-success)' : 'var(--vh-danger)' }};">{{ $allExtOk ? 'All Loaded' : 'Missing Extensions' }}</span>
            </div>
            <div class="box-body">
                <div class="row">
                    @foreach($extensionStatus as $ext => $loaded)
                    <div class="col-xs-6 col-sm-4" style="margin-bottom:8px;">
                        <span style="color:{{ $loaded ? 'var(--vh-success)' : 'var(--vh-danger)' }};">
                            <i class="fa {{ $loaded ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                        </span>
                        <code style="margin-left:4px;">{{ $ext }}</code>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Directory Permissions --}}
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-folder-open" style="margin-right:8px;color:var(--vh-accent);"></i>Directory Permissions</h3>
                <span class="pull-right label" style="background:{{ $allDirOk ? 'var(--vh-success)' : 'var(--vh-danger)' }};">{{ $allDirOk ? 'All Writable' : 'Permission Issues' }}</span>
            </div>
            <div class="box-body">
                <table class="table table-condensed" style="margin:0;">
                    <thead>
                        <tr><th>Directory</th><th>Exists</th><th>Writable</th></tr>
                    </thead>
                    <tbody>
                        @foreach($dirStatus as $label => $info)
                        <tr>
                            <td><code>{{ $label }}</code></td>
                            <td>{!! $info['exists'] ? '<i class="fa fa-check" style="color:var(--vh-success);"></i>' : '<i class="fa fa-times" style="color:var(--vh-danger);"></i>' !!}</td>
                            <td>{!! $info['writable'] ? '<i class="fa fa-check" style="color:var(--vh-success);"></i>' : '<i class="fa fa-times" style="color:var(--vh-danger);"></i>' !!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Node Connectivity --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-sitemap" style="margin-right:8px;color:var(--vh-accent);"></i>Node Connectivity ({{ $nodesOnline }}/{{ $totalNodes }} Online)</h3>
            </div>
            <div class="box-body">
                @if(count($nodeChecks) === 0)
                    <p style="text-align:center;color:var(--vh-text-muted);padding:20px;">No nodes configured.</p>
                @else
                    <div class="row">
                        @foreach($nodeChecks as $check)
                        <div class="col-md-4 col-sm-6" style="margin-bottom:12px;">
                            <div style="background:var(--vh-surface,#12121a);border:1px solid var(--vh-border,#2a2a3a);border-radius:10px;padding:15px;">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                    <strong style="color:var(--vh-text);">{{ $check['name'] }}</strong>
                                    @if($check['maintenance'])
                                        <span style="padding:3px 8px;border-radius:6px;font-size:11px;background:rgba(255,165,2,0.2);color:#ffa502;"><i class="fa fa-wrench"></i> Maint.</span>
                                    @elseif($check['status'] === 'online')
                                        <span style="padding:3px 8px;border-radius:6px;font-size:11px;background:rgba(46,213,115,0.2);color:#2ed573;"><i class="fa fa-circle"></i> Online</span>
                                    @else
                                        <span style="padding:3px 8px;border-radius:6px;font-size:11px;background:rgba(255,71,87,0.2);color:#ff4757;"><i class="fa fa-circle"></i> Offline</span>
                                    @endif
                                </div>
                                <div style="font-size:12px;color:var(--vh-text-muted);">
                                    {{ $check['fqdn'] }}
                                    @if($check['latency'])
                                        &middot; <span style="color:{{ $check['latency'] > 1000 ? 'var(--vh-warning)' : 'var(--vh-success)' }};">{{ $check['latency'] }}ms</span>
                                    @endif
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
