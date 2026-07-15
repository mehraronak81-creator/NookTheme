@extends('layouts.admin')

@section('title')
    Trash Bin
@endsection

@section('content-header')
    <h1>Trash Bin<small>Suspended servers pending review or permanent deletion</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Trash Bin</li>
    </ol>
@endsection

@section('content')

{{-- Stats --}}
<div class="row">
    <div class="col-md-4">
        <div class="box" style="text-align:center;padding:15px;">
            <h3 style="margin:0;color:var(--vh-danger);">{{ $servers->total() }}</h3>
            <small style="color:var(--vh-text-secondary);">Suspended Servers</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box" style="text-align:center;padding:15px;">
            <div style="display:flex;gap:8px;justify-content:center;">
                <form action="{{ route('admin.trashbin.empty') }}" method="POST" id="empty-trash-form">
                    {!! csrf_field() !!}
                    <input type="hidden" name="delete_all" value="1" />
                    <button type="button" class="btn btn-danger" onclick="confirmEmptyTrash()">
                        <i class="fa fa-trash"></i> Empty Entire Trash
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box" style="text-align:center;padding:15px;">
            <form action="{{ route('admin.trashbin.bulk-restore') }}" method="POST" id="bulk-restore-form">
                {!! csrf_field() !!}
                <div id="bulk-restore-inputs"></div>
                <button type="button" class="btn btn-success" onclick="bulkRestore()">
                    <i class="fa fa-undo"></i> Restore Selected
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Server List --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-trash" style="margin-right:8px;color:var(--vh-danger);"></i>Suspended Servers</h3>
                <div class="box-tools pull-right">
                    <label style="color:var(--vh-text-secondary);font-weight:normal;cursor:pointer;">
                        <input type="checkbox" id="select-all" style="margin-right:5px;"> Select All
                    </label>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:30px;"></th>
                            <th>Server Name</th>
                            <th>Owner</th>
                            <th>Node</th>
                            <th>Egg</th>
                            <th>Memory</th>
                            <th>Disk</th>
                            <th>Suspended Since</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($servers as $server)
                        <tr>
                            <td><input type="checkbox" class="server-checkbox" value="{{ $server->id }}"></td>
                            <td>
                                <a href="{{ route('admin.servers.view', $server->id) }}">
                                    <i class="fa fa-server" style="color:var(--vh-danger);margin-right:5px;"></i>
                                    {{ $server->name }}
                                </a>
                            </td>
                            <td>
                                @if($server->user)
                                    <a href="{{ route('admin.users.view', $server->user->id) }}">{{ $server->user->username }}</a>
                                @else
                                    <span style="color:var(--vh-text-muted);">Unknown</span>
                                @endif
                            </td>
                            <td>
                                @if($server->node)
                                    <a href="{{ route('admin.nodes.view', $server->node->id) }}">{{ $server->node->name }}</a>
                                @else
                                    <span style="color:var(--vh-text-muted);">Unknown</span>
                                @endif
                            </td>
                            <td>{{ $server->egg->name ?? 'Unknown' }}</td>
                            <td>{{ number_format($server->memory) }} MiB</td>
                            <td>{{ number_format($server->disk) }} MiB</td>
                            <td style="color:var(--vh-text-muted);font-size:12px;">
                                {{ $server->updated_at->diffForHumans() }}
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;">
                                    <form action="{{ route('admin.trashbin.restore', $server->id) }}" method="POST" style="display:inline;">
                                        {!! csrf_field() !!}
                                        <button type="submit" class="btn btn-xs btn-success" title="Restore"><i class="fa fa-undo"></i></button>
                                    </form>
                                    <form action="{{ route('admin.trashbin.destroy', $server->id) }}" method="POST" style="display:inline;" class="delete-form">
                                        {!! csrf_field() !!}
                                        {!! method_field('DELETE') !!}
                                        <button type="button" class="btn btn-xs btn-danger delete-btn" title="Permanently Delete"><i class="fa fa-times"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="text-align:center;color:var(--vh-text-muted);padding:40px;">
                                <i class="fa fa-check-circle" style="font-size:32px;color:var(--vh-success);display:block;margin-bottom:10px;"></i>
                                Trash bin is empty. No suspended servers.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($servers->hasPages())
                <div class="box-footer with-border">
                    <div class="col-md-12 text-center">{!! $servers->render() !!}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    // Select all checkbox
    $('#select-all').on('change', function() {
        $('.server-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Delete confirmation
    $('.delete-btn').on('click', function() {
        var form = $(this).closest('.delete-form');
        swal({
            title: 'Permanently Delete?',
            text: 'This server will be permanently destroyed. This cannot be undone!',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d9534f',
            confirmButtonText: 'Yes, delete permanently!',
            closeOnConfirm: false
        }, function() {
            form.submit();
        });
    });

    // Empty trash confirmation
    function confirmEmptyTrash() {
        swal({
            title: 'Empty Entire Trash?',
            text: 'ALL suspended servers will be permanently destroyed. This cannot be undone!',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d9534f',
            confirmButtonText: 'Yes, empty trash!',
            closeOnConfirm: false
        }, function() {
            $('#empty-trash-form').submit();
        });
    }

    // Bulk restore
    function bulkRestore() {
        var selected = [];
        $('.server-checkbox:checked').each(function() {
            selected.push($(this).val());
        });
        if (selected.length === 0) {
            swal('No Selection', 'Please select servers to restore.', 'warning');
            return;
        }
        var container = $('#bulk-restore-inputs');
        container.empty();
        selected.forEach(function(id) {
            container.append('<input type="hidden" name="servers[]" value="' + id + '">');
        });
        $('#bulk-restore-form').submit();
    }
    </script>
@endsection
