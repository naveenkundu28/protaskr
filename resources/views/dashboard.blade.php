@extends('layouts.app')
@section('title')
    Dashboard
@endsection
@section('content')
    <div class="container">
        <h2>VE Emp Tracker</h2>
        
        <div class="row mb-4">
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Tasks</h5>
                        <p class="card-text flex-grow-1">You have <strong>{{ $tasksCount }}</strong> tasks pending.</p>
                        <a href="{{ route('clients.index') }}" class="btn btn-primary mt-auto">View Tasks</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Recent Tasks</h5>
                        <ul class="list-group flex-grow-1">
                            @foreach($recentTasks as $task)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $task->title }}
                                    <span class="badge bg-primary rounded-pill">{{ $task->status == 'to_do' ? 'To Do' : 'In Progress' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
