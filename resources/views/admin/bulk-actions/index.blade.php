@extends('layouts.admin')

@section('title')
    Bulk Actions
@endsection

@section('content-header')
    <h1>Bulk Server Actions<small>Perform actions on multiple servers at once</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Bulk Actions</li>
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
                <form action="{{ route('admin.bulk-actions') }}" method="GET" class="form-inline">
                    <div class="form-group" style="margin-right:15px;">
                        <label style="margin-right:5px;">Status:</label>
                        <select name="status" class="form-control input-sm">
                            <option value="">All</option>
                            <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ $currentStatus === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="installing" {{ $currentStatus === 'installing' ? 'selected' : '' }}>Installing</option>
                            <option value="failed" {{ $currentStatus === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-right:15px;">
                        <label style="margin-right:5px;">Node:</label>
                        <select name="node" class="form-control input-sm">
                            <option value="">All Nodes</option>
                            @foreach($nodes as $node)
                                <option value="{{ $node->id }}" {{ $currentNode == $node->id ? 'selected' : '' }}>{{ $node->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search"></i> Filter</button>
                    <a href="{{ route('admin.bulk-actions') }}" class="btn btn-sm btn-default"><i class="fa fa-times"></i> Clear</a>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Action Bar --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-tasks" style="margin-right:8px;color:var(--vh-warning);"></i>Bulk Action</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('admin.bulk-actions.execute') }}" method="POST" id="bulk-action-form">
                    {!! csrf_field() !!}
                    <div id="selected-servers-inputs"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Action:</label>
                                <select name="action" class="form-control" id="bulk-action-select">
                                    <option value="">-- Select Action --</option>
                                    <option value="suspend">Suspend Selected Servers</option>
                                    <option value="unsuspend">Unsuspend Selected Servers</option>
                                    <option value="reinstall">Reinstall Selected Servers</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" style="padding-top:25px;">
                            <button type="button" class="btn btn-warning" onclick="executeBulkAction()">
                                <i class="fa fa-play"></i> Execute on <span id="selected-count">0</span> server(s)
                            </button>
                            <span style="margin-left:10px;color:var(--vh-text-muted);font-size:12px;">
                                <label style="cursor:pointer;font-weight:normal;">
                                    <input type="checkbox" id="select-all-bulk" style="margin-right:5px;"> Select All on Page
                                </label>
                            </span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Server List --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-server" style="margin-right:8px;color:var(--vh-accent);"></i>Servers ({{ $servers->total() }})</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:30px;"></th>
                            <th>Name</th>
                            <th>Owner</th>
                            <th>Node</th>
                            <th>Memory</th>
                            <th>Disk</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($servers as $server)
                        <tr>
                            <td><input type="checkbox" class="bulk-checkbox" value="{{ $server->id }}"></td>
                            <td><a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a></td>
                            <td>
                                @if($server->user)
                                    <a href="{{ route('admin.users.view', $server->user->id) }}">{{ $server->user->username }}</a>
                                @else
                                    <em>Unknown</em>
                                @endif
                            </td>
                            <td>
                                @if($server->node)
                                    {{ $server->node->name }}
                                @else
                                    <em>Unknown</em>
                                @endif
                            </td>
                            <td>{{ number_format($server->memory) }} MiB</td>
                            <td>{{ number_format($server->disk) }} MiB</td>
                            <td>
                                @if($server->isSuspended())
                                    <span class="label bg-maroon">Suspended</span>
                                @elseif(!$server->isInstalled())
                                    <span class="label label-warning">Installing</span>
                                @elseif($server->status === \Pterodactyl\Models\Server::STATUS_INSTALL_FAILED || $server->status === \Pterodactyl\Models\Server::STATUS_REINSTALL_FAILED)
                                    <span class="label label-danger">Failed</span>
                                @else
                                    <span class="label label-success">Active</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center;color:var(--vh-text-muted);padding:30px;">No servers match the current filters.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($servers->hasPages())
                <div class="box-footer with-border">
                    <div class="col-md-12 text-center">{!! $servers->appends(['status' => $currentStatus, 'node' => $currentNode])->render() !!}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    // Update selected count
    function updateCount() {
        var count = $('.bulk-checkbox:checked').length;
        $('#selected-count').text(count);
    }

    $('.bulk-checkbox').on('change', updateCount);

    $('#select-all-bulk').on('change', function() {
        $('.bulk-checkbox').prop('checked', $(this).prop('checked'));
        updateCount();
    });

    function executeBulkAction() {
        var action = $('#bulk-action-select').val();
        var selected = [];
        $('.bulk-checkbox:checked').each(function() {
            selected.push($(this).val());
        });

        if (!action) {
            swal('Error', 'Please select an action.', 'error');
            return;
        }
        if (selected.length === 0) {
            swal('Error', 'Please select at least one server.', 'warning');
            return;
        }

        var actionNames = {
            'suspend': 'SUSPEND',
            'unsuspend': 'UNSUSPEND',
            'reinstall': 'REINSTALL'
        };

        swal({
            title: 'Confirm Bulk Action',
            text: 'Are you sure you want to ' + actionNames[action] + ' ' + selected.length + ' server(s)? This action may not be easily reversible.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d9534f',
            confirmButtonText: 'Yes, execute!',
            closeOnConfirm: false
        }, function() {
            var container = $('#selected-servers-inputs');
            container.empty();
            selected.forEach(function(id) {
                container.append('<input type="hidden" name="servers[]" value="' + id + '">');
            });
            $('#bulk-action-form').submit();
        });
    }
    </script>
@endsection
