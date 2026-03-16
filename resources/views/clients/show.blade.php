@extends('layouts.app')
@section('title')
    {{ $client->name }} - Project Details
@endsection
@section('content')
    <div class="container-fluid">
        <h2 class="mb-4 shadow-sm p-3 rounded bg-white text-center"> {{ $client->name }}</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-7">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">{{ $client->name }}</h5>
                        <p class="card-text">{{ $client->description }}</p>
                        <p class="card-text"><strong>Start Date:</strong>
                            {{ \Carbon\Carbon::parse($client->start_date)->format('j F Y') }}</p>
                        <p class="card-text"><strong>End Date:</strong>
                            {{ \Carbon\Carbon::parse($client->end_date)->format('j F Y') }}</p>
                        <p class="card-text"><strong>Status:</strong>
                            {{ $client->status == 'pending' ? 'Pending' : ($client->status == 'on_going' ? 'In Progress' : 'Completed') }}
                        </p>
                        <p class="card-text"><strong>Budget:</strong> ${{ $client->budget }}</p>

                        <h5 class="mt-4">Project Progress</h5>
                        @php
                            $totalTasks = $client->tasks->count();
                            $completedTasks = $client->tasks->where('status', 'completed')->count();
                            $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
                        @endphp
                        <div class="progress mb-4">
                            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;"
                                aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                {{ round($progress) }}%</div>
                        </div>

                        <a href="{{ route('clients.index') }}" class="btn btn-secondary mt-3">Back to Projects</a>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title"> Team Members </h5>
                            @can('project-edit')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addMemberModal"> <i class="bi bi-plus-circle"></i> </button>
                                @endcan
                        </div>

                        <div class="row">
                            @foreach ($teamMembers as $user)
                                <div class="col-12">
                                    <div class="card mb-3">
                                        <div class="row g-0">
                                            <div class="col-md-12">
                                                <div class="card-body d-flex justify-content-between">
                                                    <div>
                                                    <p class="card-title fw-bolder">{{ $user->name }}</p>
                                                    <p class="card-text">{{ $user->email }}</p>
                                                    </div>
                                                    @can('project-edit')
                                                    <div>
                                                        <form action="{{ route('clients.removeMember') }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            <input type="hidden" name="project_id" value="{{ $client->id }}">
                                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                            
                                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                                    onclick="return confirm('Are you sure you want to remove this member?')"> <i class="bi bi-trash"></i> </button>
                                                        </form>
                                                    </div>
                                                    @endcan

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @can('project-edit')
    <div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMemberModalLabel">Add Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('clients.addMember')}}" method="POST">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $client->id }}">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Select User</label>
                            <select class="form-select" name="user_id" id="">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Member</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endcan
@endsection
