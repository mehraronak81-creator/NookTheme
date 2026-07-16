@extends('layouts.admin')

@section('title')
    Announcements
@endsection

@section('content-header')
    <h1>Announcements<small>Broadcast messages shown to users on their dashboard.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Announcements</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Announcement List</h3>
                <div class="box-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newAnnouncementModal">Create New</button>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th class="text-center">Level</th>
                            <th class="text-center">Priority</th>
                            <th class="text-center">Active</th>
                            <th>Window</th>
                        </tr>
                        @foreach ($announcements as $announcement)
                            <tr>
                                <td><code>{{ $announcement->id }}</code></td>
                                <td><a href="{{ route('admin.announcements.edit', $announcement->id) }}">{{ $announcement->title }}</a></td>
                                <td class="text-center"><span class="label label-{{ $announcement->level === 'error' ? 'danger' : ($announcement->level === 'success' ? 'success' : ($announcement->level === 'warning' ? 'warning' : 'info')) }}">{{ $announcement->level }}</span></td>
                                <td class="text-center">{{ $announcement->priority }}</td>
                                <td class="text-center">
                                    @if ($announcement->active)
                                        <i class="fa fa-check text-green"></i>
                                    @else
                                        <i class="fa fa-times text-red"></i>
                                    @endif
                                </td>
                                <td>
                                    {{ $announcement->starts_at ? $announcement->starts_at->toDayDateTimeString() : 'Always' }}
                                    &ndash;
                                    {{ $announcement->ends_at ? $announcement->ends_at->toDayDateTimeString() : 'Forever' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="newAnnouncementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.announcements') }}" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Create Announcement</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="pTitle" class="form-label">Title</label>
                            <input type="text" name="title" id="pTitle" class="form-control" />
                        </div>
                        <div class="col-md-12" style="margin-top:10px;">
                            <label for="pBody" class="form-label">Body</label>
                            <textarea name="body" id="pBody" class="form-control" rows="4"></textarea>
                            <p class="text-muted small">Shown to users as the announcement message. Markdown is supported.</p>
                        </div>
                        <div class="col-md-6" style="margin-top:10px;">
                            <label for="pLevel" class="form-label">Level</label>
                            <select name="level" id="pLevel" class="form-control">
                                <option value="info">Info</option>
                                <option value="success">Success</option>
                                <option value="warning">Warning</option>
                                <option value="error">Error</option>
                            </select>
                        </div>
                        <div class="col-md-6" style="margin-top:10px;">
                            <label for="pPriority" class="form-label">Priority</label>
                            <input type="number" name="priority" id="pPriority" class="form-control" value="0" />
                            <p class="text-muted small">Higher numbers are shown first.</p>
                        </div>
                        <div class="col-md-6" style="margin-top:10px;">
                            <label for="pStartsAt" class="form-label">Starts At <span class="text-muted">(optional)</span></label>
                            <input type="datetime-local" name="starts_at" id="pStartsAt" class="form-control" />
                        </div>
                        <div class="col-md-6" style="margin-top:10px;">
                            <label for="pEndsAt" class="form-label">Ends At <span class="text-muted">(optional)</span></label>
                            <input type="datetime-local" name="ends_at" id="pEndsAt" class="form-control" />
                        </div>
                        <div class="col-md-12" style="margin-top:10px;">
                            <label class="form-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {!! csrf_field() !!}
                    <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
