@extends('layouts.admin')

@section('title')
    Announcement &mdash; {{ $announcement->title }}
@endsection

@section('content-header')
    <h1>{{ $announcement->title }}<small>Edit this announcement.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.announcements') }}">Announcements</a></li>
        <li class="active">{{ $announcement->id }}</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('admin.announcements.edit', $announcement->id) }}" method="POST">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Announcement</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="pTitle" class="control-label">Title</label>
                            <input type="text" name="title" id="pTitle" class="form-control" value="{{ $announcement->title }}" />
                        </div>
                        <div class="form-group col-md-12">
                            <label for="pBody" class="control-label">Body</label>
                            <textarea name="body" id="pBody" class="form-control" rows="5">{{ $announcement->body }}</textarea>
                            <p class="text-muted small">Markdown is supported.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pLevel" class="control-label">Level</label>
                            <select name="level" id="pLevel" class="form-control">
                                @foreach (['info' => 'Info', 'success' => 'Success', 'warning' => 'Warning', 'error' => 'Error'] as $value => $label)
                                    <option value="{{ $value }}" @if ($announcement->level === $value) selected @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pPriority" class="control-label">Priority</label>
                            <input type="number" name="priority" id="pPriority" class="form-control" value="{{ $announcement->priority }}" />
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pStartsAt" class="control-label">Starts At</label>
                            <input type="datetime-local" name="starts_at" id="pStartsAt" class="form-control" value="{{ $announcement->starts_at ? $announcement->starts_at->format('Y-m-d\TH:i') : '' }}" />
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pEndsAt" class="control-label">Ends At</label>
                            <input type="datetime-local" name="ends_at" id="pEndsAt" class="form-control" value="{{ $announcement->ends_at ? $announcement->ends_at->format('Y-m-d\TH:i') : '' }}" />
                        </div>
                        <div class="form-group col-md-12">
                            <label class="control-label"><input type="checkbox" name="active" value="1" @if ($announcement->active) checked @endif /> Active</label>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    {!! method_field('PATCH') !!}
                    <button type="submit" name="action" value="edit" class="btn btn-primary pull-right">Save</button>
                    <button type="submit" name="action" value="delete" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
