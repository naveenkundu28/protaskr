@extends('layouts.app')

@section('content')
<div class="container mt-80">
    <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
        <h2>Uploaded Files</h2>
        @if (auth()->user()->can('tasks-update', $task) && $task->status !== 'completed' && $task->story?->status !== 'completed')
            <a href="{{ route('tasks.files.create', $task->id) }}" class="btn btn-primary">Upload File</a>
        @endif
    </div>

    @if ($task->status === 'completed' || $task->story?->status === 'completed')
        <div class="alert alert-info" role="status">
            @if ($task->story?->status === 'completed')
                This story is completed and its task files are locked. Reopen the story to make changes.
            @else
                This task is completed and its files are locked.
            @endif
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        @foreach($files as $file)
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $file->name }}</h5>
                        <p class="card-text"><strong>Type:</strong> {{ $file->type }}</p>
                        <a href="{{ route('tasks.files.show', [$task->id, $file->id]) }}" target="_blank" class="btn btn-primary"> <i class="bi bi-eye"></i> </a>
                        <a href="{{ route('tasks.files.download', [$task->id, $file->id]) }}" class="btn btn-info text-white"> <i class="bi bi-download"></i> </a>
                        @if (auth()->user()->can('tasks-update', $task) && $task->status !== 'completed' && $task->story?->status !== 'completed')
                            <a href="{{ route('tasks.files.edit', [$task->id, $file->id]) }}" class="btn btn-warning"> <i class="bi bi-pencil-square"></i> </a>
                            <form action="{{ route('tasks.files.destroy', [$task->id, $file->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"> <i class="bi bi-trash"></i> </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
