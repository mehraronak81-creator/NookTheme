@extends('layouts.admin')

@section('title')
    Maintenance Mode
@endsection

@section('content-header')
    <h1>Maintenance Mode<small>Toggle maintenance mode on nodes</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Maintenance</li>
    </ol>
@endsection

@section('content')

{{-- Global Actions --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-wrench" style="margin-right:8px;color:var(--vh-warning);"></i>Global Maintenance Controls</h3>
            </div>
            <div class="box-body">
                <p style="color:var(--vh-text-secondary);margin-bottom:15px;">
                    Maintenance mode prevents new servers from being deployed to a node and shows a warning to users accessing servers on that node. 
                    Existing servers will continue to run but users will be warned.
                </p>
                <div class="row">
                    <div class="col-sm-6 text-center" style="margin-bottom:10px;">
                        <form action="{{ route('admin.maintenance.enable-all') }}" method="POST" id="enable-all-form">
                            {!! csrf_field() !!}
                            <button type="button" class="btn btn-warning btn-lg" onclick="confirmAction('enable maintenance on ALL nodes', 'enable-all-form')">
                                <i class="fa fa-pause-circle"></i> Enable Maintenance on ALL Nodes
                            </button>
                        </form>
                    </div>
                    <div class="col-sm-6 text-center" style="margin-bottom:10px;">
                        <form action="{{ route('admin.maintenance.disable-all') }}" method="POST" id="disable-all-form">
                            {!! csrf_field() !!}
                            <button type="button" class="btn btn-success btn-lg" onclick="confirmAction('disable maintenance on ALL nodes', 'disable-all-form')">
                                <i class="fa fa-play-circle"></i> Disable Maintenance on ALL Nodes
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Node List --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-sitemap" style="margin-right:8px;color:var(--vh-accent);"></i>Node Maintenance Status</h3>
                <span class="pull-right">
                    <span class="label" style="background:var(--vh-warning);margin-right:5px;">{{ $nodes->where('maintenance_mode', true)->count() }} in maintenance</span>
                    <span class="label" style="background:var(--vh-success);">{{ $nodes->where('maintenance_mode', false)->count() }} active</span>
                </span>
            </div>
            <div class="box-body">
                <div class="row">
                    @forelse($nodes as $node)
                    <div class="col-md-4 col-sm-6" style="margin-bottom:15px;">
                        <div style="background:var(--vh-surface);border:1px solid {{ $node->maintenance_mode ? 'rgba(255,165,2,0.4)' : 'var(--vh-border)' }};border-radius:12px;padding:20px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                                <div>
                                    <strong style="color:var(--vh-text);font-size:15px;">{{ $node->name }}</strong>
                                    <div style="font-size:11px;color:var(--vh-text-muted);margin-top:2px;">{{ $node->fqdn }}</div>
                                </div>
                                @if($node->maintenance_mode)
                                    <span style="padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;background:rgba(255,165,2,0.2);color:#ffa502;">
                                        <i class="fa fa-wrench"></i> Maintenance
                                    </span>
                                @else
                                    <span style="padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;background:rgba(46,213,115,0.2);color:#2ed573;">
                                        <i class="fa fa-check-circle"></i> Active
                                    </span>
                                @endif
                            </div>

                            <div style="font-size:12px;color:var(--vh-text-muted);margin-bottom:12px;">
                                <i class="fa fa-map-marker"></i> {{ $node->location->short ?? 'Unknown' }}
                                &middot; <i class="fa fa-server"></i> {{ $node->servers_count }} servers
                                &middot; <i class="fa fa-microchip"></i> {{ number_format($node->memory) }} MiB RAM
                            </div>

                            <form action="{{ route('admin.maintenance.toggle', $node->id) }}" method="POST">
                                {!! csrf_field() !!}
                                @if($node->maintenance_mode)
                                    <button type="submit" class="btn btn-success btn-sm" style="width:100%;">
                                        <i class="fa fa-play"></i> Disable Maintenance Mode
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-warning btn-sm" style="width:100%;">
                                        <i class="fa fa-pause"></i> Enable Maintenance Mode
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="col-xs-12">
                        <p style="text-align:center;color:var(--vh-text-muted);padding:30px;">
                            No nodes configured. <a href="{{ route('admin.nodes.new') }}">Add a node</a> to manage maintenance mode.
                        </p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    function confirmAction(text, formId) {
        swal({
            title: 'Confirm',
            text: 'Are you sure you want to ' + text + '?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, do it!',
            closeOnConfirm: false
        }, function() {
            $('#' + formId).submit();
        });
    }
    </script>
@endsection
