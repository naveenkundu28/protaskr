@extends('layouts.app')
@section('title')
    Projects
@endsection
@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center bg-white mb-4 shadow-sm p-3 rounded">
            <h2>{{ request('type') == 'archived' ? 'Archived Projects' : 'Projects' }}</h2>
            <div>

                @if (!request('type'))
                    @can('project-create')
                        <a href="{{ route('projects.create') }}" class="btn btn-primary">Add Project</a>
                    @endcan
                @endif
                <button class="btn btn-secondary" onclick="changeView('cards')" id="cardsbtn"><i class="bi bi-grid"></i></button>
                <button class="btn btn-success" onclick="changeView('table')" id="tablebtn"><i class="bi bi-list-task"></i></button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div style="display: none" class="row" id="cards">
            @foreach ($projects as $project)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $project->name }}</h5>
                            <p class="card-text">{{ $project->description }}</p>
                            <p class="card-text">
                                <strong>Status:</strong>
                                {{ $project->status == 'pending' ? 'Pending' : ($project->status == 'on_going' ? 'In Progress' : 'Completed') }}</p>
                               <p class="card-text"> <strong>Assigned to:</strong>
                                @isset($project->users)
                                    @foreach ($project->users as $user)
                                        <br> <span class="">{{ $user->name }}</span>
                                    @endforeach
                                @endisset
                            </p>
                            <p class="card-text">
                                <strong>Pending Tasks:</strong>
                                {{$project->to_do_tasks + $project->in_progress_tasks + $project->in_review_tasks}}   
                            </p>
                            
                            @if (request('type') == 'archived')
                                <form action="{{ route('projects.restore', $project->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success"
                                        onclick="return confirm('Are you sure you want to restore this project?')">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}"
                                    class="btn btn-primary">
                                    <i class="bi bi-list"></i>
                                </a>
                                <a href="{{ route('projects.show', $project->id) }}" class="btn btn-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('project-edit')
                                    <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-warning">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endcan

                                @can('project-delete')
                                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this project?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            {!! $projects->links('pagination::bootstrap-5') !!}
        </div>

        {{-- Table view --}}
        <div id="table">
        <table class="table table-bordered">
            <tr>
                <th>No</th>
                <th>Title</th>
                <th>Description</th>
                <th>Assigned To</th>
                <th>Pending Tasks</th>
                <th>Status</th>
                <th width="180px">Action</th>
            </tr>
            @foreach ($projects as $project)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td> {{ $project->name }}</td>
                    <td>{{ $project->description }}</td>
                    <td> @isset($project->users)
                            @foreach ($project->users as $user)
                                <br> <span class="">{{ $user->name }}</span>
                            @endforeach
                        @endisset
                    </td>
                    <td>{{$project->to_do_tasks + $project->in_progress_tasks + $project->in_review_tasks}}</td>

                    <td>{{ $project->status == 'pending' ? 'Pending' : ($project->status == 'on_going' ? 'In Progress' : 'Completed') }}
                    </td>

                    <td>
                        @if (request('type') == 'archived')
                            <form action="{{ route('projects.restore', $project->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success"
                                    onclick="return confirm('Are you sure you want to restore this project?')">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}" class="btn btn-primary">
                                <i class="bi bi-list"></i>
                            </a>
                            <a href="{{ route('projects.show', $project->id) }}" class="btn btn-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('project-edit')
                                <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-warning">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan

                            @can('project-delete')
                                <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this project?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
        {!! $projects->links('pagination::bootstrap-5') !!}
        </div>
        {{--  --}}
    </div>
    <script>
        function changeView(type) {
            if (type == 'table') {
                $('#table').show();
                $('#cards').hide();
                $('#tablebtn').addClass('btn-success');
                $('#cardsbtn').addClass('btn-secondary');
                $('#cardsbtn').removeClass('btn-success');
            } else {
                $('#table').hide();
                $('#cards').show();
                $('#cardsbtn').addClass('btn-success');
                $('#tablebtn').addClass('btn-secondary');
                $('#tablebtn').removeClass('btn-success');
            }
        }
    </script>
@endsection
