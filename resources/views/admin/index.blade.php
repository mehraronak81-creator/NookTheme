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
                    Your panel is <strong>not up-to-date!</strong> The latest version is <a href="https://github.com/Pterodactyl/Panel/releases/v{{ $version->getPanel() }}" target="_blank"><code>{{ $version->getPanel() }}</code></a> and you are currently running version <code>{{ config('app.version') }}</code>. You can find instructions on how to update your panel <a href="https://github.com/Nookure/NookTheme">here</a>.
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
                <h3>{{ $servers ?? '0' }}</h3>
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
                <h3>{{ $nodes ?? '0' }}</h3>
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
                <h3>{{ $users ?? '0' }}</h3>
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
                <h3>{{ $eggs ?? '0' }}</h3>
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
                        <a href="{{ route('admin.settings') }}"><button class="btn btn-default" style="width:100%;"><i class="fa fa-fw fa-cog"></i> Settings</button></a>
                    </div>
                </div>
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
