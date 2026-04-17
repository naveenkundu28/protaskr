@extends('layouts.app')
@section('title')
    Projects
@endsection
@section('content')
    <div class="container-fluid">
        <style>
            .type-cell-neutral {
                background-color: #f1f3f5 !important;
                color: #495057 !important;
            }

            .type-cell-project {
                background-color: #fff3e0 !important;
                color: #8a4b08 !important;
            }
        </style>
        <div class="d-flex justify-content-between align-items-center bg-white mb-4 shadow-sm p-3 rounded">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h2 class="mb-0">{{ request('type') == 'archived' ? 'Archived Clients' : 'Clients' }}</h2>
                <form method="GET" action="{{ route('clients.index') }}" class="d-flex gap-2 align-items-center">

                    {{-- Preserve tab (ongoing/completed/archived) --}}
                    @if (request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif

                    {{-- Search --}}
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search Clients">

                    {{-- Type Filter --}}
                    <select name="type_filter[]" class="form-control select2" multiple>
                        <option value="internal_team"
                            {{ collect(request('type_filter'))->contains('internal_team') ? 'selected' : '' }}>
                            Internal Team
                        </option>
                        <option value="external_team"
                            {{ collect(request('type_filter'))->contains('external_team') ? 'selected' : '' }}>
                            External Team
                        </option>
                        <option value="client" {{ collect(request('type_filter'))->contains('client') ? 'selected' : '' }}>
                            Client
                        </option>
                        <option value="project"
                            {{ collect(request('type_filter'))->contains('project') ? 'selected' : '' }}>
                            Project
                        </option>
                    </select>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-primary">Filter</button>

                    {{-- Clear --}}
                    <a href="{{ route('clients.index', request()->only('type')) }}" class="btn btn-secondary">
                        Clear
                    </a>

                </form>



            </div>
            <div class="align-items-center d-flex gap-2">

                @if (!request('type'))
                    @can('project-create')
                        <a href="{{ route('clients.create') }}" class="btn btn-primary">Add Client</a>
                    @endcan
                @endif
                <button class="btn btn-secondary" onclick="changeView('cards')" id="cardsbtn"><i
                        class="bi bi-grid"></i></button>
                <button class="btn btn-success" onclick="changeView('table')" id="tablebtn"><i
                        class="bi bi-list-task"></i></button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <ul class="nav nav-pills service-tabs mb-3">

            <li class="nav-item">
                <a class="nav-link {{ !request('type') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                    Ongoing
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request('type') == 'completed' ? 'active' : '' }}"
                    href="{{ route('clients.index', ['type' => 'completed']) }}">
                    Completed
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request('type') == 'archived' ? 'active' : '' }}"
                    href="{{ route('clients.index', ['type' => 'archived']) }}">
                    Archived
                </a>
            </li>

        </ul>
        <div style="display: none" class="row" id="cards">
            @foreach ($projects as $project)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $project->name }}</h5>
                            @if ($project->client_email)
                                <p class="card-text">
                                    <strong>Email:</strong> {{ $project->client_email }}
                                </p>
                            @endif
                            {{-- @if ($project->client_code)
                                <p class="card-text">
                                    <strong>Code:</strong> {{ $project->client_code }}
                                </p>
                            @endif --}}

                            @if ($project->project_name)
                                <p class="card-text">
                                    <strong>Project Name:</strong> {{ $project->project_name }}
                                </p>
                            @endif

                            @if ($project->start_date)
                                <p class="card-text">
                                    <strong>Start Date:</strong>
                                    {{ \Carbon\Carbon::parse($project->start_date)->format('j F Y') }}
                                </p>
                            @endif

                            @if ($project->type)
                                <p class="card-text">
                                    <strong>Type:</strong>
                                    {{ $project->type == 'internal_team'
                                        ? 'Internal Team'
                                        : ($project->type == 'external_team'
                                            ? 'External Team'
                                            : ($project->type == 'client'
                                            ? 'Client'
                                            : ($project->type == 'project'
                                                ? 'Project'
                                                : ''))) }}
                                </p>
                            @endif

                            {{-- <p class="card-text">{{ $project->description }}</p> --}}
                            <p class="card-text">
                                <strong>Status:</strong>
                                {{ $project->status == 'not_started' ? 'Not Started' : ($project->status == 'in_progress' ? 'In Progress' : 'Completed') }}
                            </p>
                            <p class="card-text"> <strong>Assigned to:</strong>
                                @isset($project->users)
                                    {{-- @foreach ($project->users as $user)
                                        <br> <span class="">{{ $user->name }}</span>
                                    @endforeach --}}
                                    @php
                                        $users = $project->users;
                                        $visibleUsers = $users->take(2);
                                        $remainingUsers = $users->slice(2);
                                        $remainingCount = $users->count() - 2;
                                    @endphp

                                    <span>
                                        {{-- Visible Users --}}
                                        @foreach ($visibleUsers as $index => $user)
                                            <span class="{{ $user->trashed() ? 'text-danger' : '' }}">
                                                {{ $user->name }}
                                            </span>{{ !$loop->last ? ',' : '' }}
                                        @endforeach

                                        {{-- Remaining Count --}}
                                        @if ($remainingCount > 0)
                                            <span class="text-primary" data-bs-toggle="tooltip" style="cursor: pointer;"
                                                title="@foreach ($remainingUsers as $user){{ $user->name }}{{ !$loop->last ? ', ' : '' }} @endforeach">
                                                +{{ $remainingCount }}
                                            </span>
                                        @endif
                                    </span>
                                @endisset
                            </p>
                            {{-- <p class="card-text">
                                <strong>Pending Tasks:</strong>
                                {{ $project->to_do_tasks + $project->in_progress_tasks + $project->in_review_tasks }}
                            </p> --}}

                            @if (request('type') == 'archived')
                                <form action="{{ route('clients.restore', $project->id) }}" method="POST"
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
                                <a href="{{ route('clients.show', $project->id) }}" class="btn btn-primary">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @can('service-show')
                                    <a href="{{ route('clients.services.index', $project->id) }}" class="btn btn-info">
                                        <i class="bi bi-gear"></i>
                                    </a>
                                @endcan



                                {{-- @can('project-edit') --}}
                                @if (auth()->user()->can('project-edit') || auth()->user()->hasRole('Employee'))
                                    <a href="{{ route('clients.edit', $project->id) }}" class="btn btn-warning">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endif
                                {{-- @endcan --}}

                                @can('project-delete')
                                    <form action="{{ route('clients.destroy', $project->id) }}" method="POST"
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
        <div id="tableSearchResults">
            <div id="table">

                <table class="table table-bordered">
                    <tr>
                        <th>No</th>
                        <th>Client Name</th>
                        <th>Client Email</th>
                        {{-- <th>Client Code</th> --}}
                        <th>Project Name</th>
                        {{-- <th>Description</th> --}}
                        <th>Assigned To</th>
                        <th>Start Date</th>
                        <th>Hours (Current Month)</th>
                        {{-- <th>Pending Tasks</th> --}}
                        <th>Status</th>
                        <th width="180px">Action</th>
                    </tr>


                    @foreach ($projects as $project)
                        @php
                            $rowTypeClass = in_array($project->type, ['internal_team', 'external_team'], true)
                                ? 'type-cell-neutral'
                                : ($project->type === 'project' ? 'type-cell-project' : '');
                        @endphp
                        <tr>
                            <td class="{{ $rowTypeClass }}">{{ $loop->iteration }}</td>
                            <td class="{{ $rowTypeClass }}"> @isset($project)
                                    <span>{{ $project->name }}</span>
                                @endisset
                            </td>
                            <td class="{{ $rowTypeClass }}"> @isset($project)
                                    <span>{{ $project->client_email }}</span>
                                @endisset
                            </td>
                            {{-- <td> @isset($project)
                                <span>{{ $project->client_code }}</span>
                            @endisset
                        </td> --}}
                            <td class="{{ $rowTypeClass }}"> {{ $project->project_name }}</td>
                            {{-- <td>{{ $project->description }}</td> --}}
                            <td class="{{ $rowTypeClass }}"> @isset($project->users)
                                    {{-- @foreach ($project->users as $user)
                                        <span
                                            class="{{ $user->trashed() ? 'text-danger' : '' }}">{{ $user->name }}</span><br>
                                    @endforeach --}}
                                    @php
                                        $users = $project->users;
                                        $visibleUsers = $users->take(2);
                                        $remainingUsers = $users->slice(2);
                                        $remainingCount = $users->count() - 2;
                                    @endphp

                                    <span>
                                        {{-- Visible Users --}}
                                        @foreach ($visibleUsers as $index => $user)
                                            <span class="{{ $user->trashed() ? 'text-danger' : '' }}">
                                                {{ $user->name }}
                                            </span>{{ !$loop->last ? ',' : '' }}
                                        @endforeach

                                        {{-- Remaining Count --}}
                                        @if ($remainingCount > 0)
                                            <span class="text-primary" data-bs-toggle="tooltip" style="cursor: pointer;"
                                                title="@foreach ($remainingUsers as $user){{ $user->name }}{{ !$loop->last ? ', ' : '' }} @endforeach">
                                                +{{ $remainingCount }}
                                            </span>
                                        @endif
                                    </span>
                                @endisset
                            </td>
                            <td class="{{ $rowTypeClass }}">
                                {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('j F Y') : '' }}
                            </td>
                            <td class="{{ $rowTypeClass }}">{{ number_format((float) ($project->current_month_hours ?? 0), 2) }}</td>
                            {{-- <td>{{$project->to_do_tasks + $project->in_progress_tasks + $project->in_review_tasks}}</td> --}}
                            <td class="{{ $rowTypeClass }}">{{ $project->status == 'not_started' ? 'Not Started' : ($project->status == 'in_progress' ? 'In Progress' : 'Completed') }}
                            </td>

                            <td class="{{ $rowTypeClass }}">
                                <div class="dropdown service-action-dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="bi bi-list-task"></i>
                                    </button>
                                    <ul class="dropdown-menu py-0">
                                        @if (request('type') == 'archived')
                                            <li class="px-2 py-2">
                                                <form action="{{ route('clients.restore', $project->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="border-0 text-danger text-start w-100 bg-white"
                                                        onclick="return confirm('Are you sure you want to restore this project?')">
                                                        Restore
                                                    </button>
                                                </form>
                                            </li>
                                        @else
                                            <li class="px-2 py-2">
                                                <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}"
                                                    class="w-100 text-decoration-none d-block">
                                                    Tasks
                                                </a>
                                            </li>

                                            <li>
                                                <hr class="my-1" />
                                            </li>
                                            @can('service-show')
                                                <li class="px-2 py-2">
                                                    <a href="{{ route('clients.services.index', $project->id) }}"
                                                        class="w-100 text-decoration-none text-warning d-block">
                                                        Services
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="my-1" />
                                                </li>
                                            @endcan

                                            <li class="px-2 py-2">
                                                <a href="{{ route('clients.show', $project->id) }}"
                                                    class="w-100 text-decoration-none text-success d-block">
                                                    View
                                                </a>
                                            </li>

                                            {{-- @can('project-edit') --}}
                                            @if (auth()->user()->can('project-edit') || auth()->user()->hasRole('Employee'))
                                                <li>
                                                    <hr class="my-1" />
                                                </li>

                                                <li class="px-2 py-2">
                                                    <a href="{{ route('clients.edit', $project->id) }}"
                                                        class="w-100 text-decoration-none text-info d-block">
                                                        Edit
                                                    </a>
                                                </li>
                                            @endif
                                            {{-- @endcan --}}

                                            @can('project-delete')
                                                <li>
                                                    <hr class="my-1" />
                                                </li>

                                                <li class="py-2">
                                                    <form action="{{ route('clients.destroy', $project->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="border-0 text-danger text-start w-100 bg-white"
                                                            onclick="return confirm('Are you sure you want to delete this project?')">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            @endcan
                                        @endif

                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </table>
                {!! $projects->links('pagination::bootstrap-5') !!}
            </div>
        </div>
        
        <h5 class="mt-4">Client Legend</h5>
        <div class="d-flex flex-wrap gap-3 pb-4">
            <div style="background: #f1f3f5; color: #495057; padding:6px 12px; border-radius:4px; border: 1px solid #000;">Internal Team</div>
            <div style="background: #f1f3f5; color: #495057; padding:6px 12px; border-radius:4px; border: 1px solid #000;">External Team</div>
            <div style="background: #fff3e0; color: #8a4b08; padding:6px 12px; border-radius:4px; border: 1px solid #000;">Project</div>
            <div style="background: #fff; color: #000; padding:6px 12px; border-radius:4px; border: 1px solid #000;">Client</div>
        </div>


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
