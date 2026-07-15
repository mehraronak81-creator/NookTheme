@extends('layouts.admin')

@section('title')
    Webhooks
@endsection

@section('content-header')
    <h1>Webhooks<small>External notifications & integrations</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Webhooks</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-plus" style="margin-right:8px;color:var(--vh-accent);"></i>Create Webhook</h3>
            </div>
            <form action="{{ route('admin.webhooks.store') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Discord Notifications" required>
                    </div>
                    <div class="form-group">
                        <label>URL</label>
                        <input type="url" name="url" class="form-control" placeholder="https://discord.com/api/webhooks/..." required>
                    </div>
                    <div class="form-group">
                        <label>Events</label>
                        <div class="row">
                            @foreach(['server.created', 'server.deleted', 'server.suspended', 'user.created', 'user.deleted', 'node.offline', 'backup.completed'] as $evt)
                            <div class="col-md-4">
                                <label style="font-weight:normal;color:var(--vh-text-secondary);">
                                    <input type="checkbox" name="events[]" value="{{ $evt }}"> {{ str_replace('.', ' ', ucfirst($evt)) }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Create Webhook</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle" style="margin-right:8px;"></i>About Webhooks</h3>
            </div>
            <div class="box-body">
                <p style="color:var(--vh-text-secondary);">Webhooks send HTTP POST requests to your configured URL when events occur. Use them to integrate with Discord, Slack, or custom systems.</p>
                <p style="color:var(--vh-text-secondary);">Each webhook includes a secret header (<code>X-Webhook-Secret</code>) for verification.</p>
            </div>
        </div>
    </div>
</div>

{{-- Existing Webhooks --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list" style="margin-right:8px;"></i>Configured Webhooks ({{ count($webhooks) }})</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead><tr><th>Status</th><th>Name</th><th>URL</th><th>Events</th><th>Created By</th><th>Triggers</th><th>Actions</th></tr></thead>
                    <tbody>
                    @forelse($webhooks as $wh)
                        <tr>
                            <td>{!! $wh['active'] ? '<span class="label" style="background:var(--vh-success);">Active</span>' : '<span class="label label-default">Disabled</span>' !!}</td>
                            <td><strong>{{ $wh['name'] }}</strong></td>
                            <td><code style="font-size:11px;">{{ \Illuminate\Support\Str::limit($wh['url'], 40) }}</code></td>
                            <td>
                                @foreach($wh['events'] as $e)
                                    <span class="label" style="background:var(--vh-surface-2);color:var(--vh-text-secondary);font-size:10px;">{{ $e }}</span>
                                @endforeach
                            </td>
                            <td>{{ $wh['created_by'] }}</td>
                            <td>{{ $wh['trigger_count'] }}</td>
                            <td>
                                <button class="btn btn-xs btn-default" onclick="testWebhook('{{ $wh['id'] }}')"><i class="fa fa-bolt"></i> Test</button>
                                <form action="{{ route('admin.webhooks.toggle', $wh['id']) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-xs {{ $wh['active'] ? 'btn-warning' : 'btn-success' }}">
                                        <i class="fa fa-{{ $wh['active'] ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.webhooks.destroy', $wh['id']) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Delete this webhook?')"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center" style="color:var(--vh-text-muted);">No webhooks configured</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function testWebhook(id) {
    $.post('/admin/webhooks/test/' + id, {_token: $('meta[name="_token"]').attr('content')}, function(res) {
        if (res.success) {
            alert('Webhook test successful! Status: ' + res.status);
        } else {
            alert('Webhook test failed: ' + (res.error || 'Unknown error'));
        }
    }).fail(function() { alert('Failed to send test.'); });
}
</script>
@endsection
