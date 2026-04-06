@extends('layouts.app')
@section('title')
    {{ isset($project) ? $project->name : 'All Tasks' }}
@endsection
@section('content')
    <div class="container-fluid">
        <div style="padding: 0px 12px;">
            <div class="row">
                <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                    <h2> {{ isset($project) ? $project->name : 'All' }} - Tasks</h2>
                    <div>
                        <a class="btn btn-warning"
                        href="{{ route('tasks.index', ['filter' => 1] + (isset($project) ? ['project_id' => $project->id] : [])) }}">
                        Filters
                    </a>
                    
                        <a href="#" data-bs-toggle="modal" data-bs-target="#exportModal" class="btn btn-secondary"
                            data-status="to_do">Export</a>
                            @can('mark-task-complete')
                            <button id="markCompleteBtn" onclick="submitCompletedTasks()" disabled type="submit"
                                class="btn btn-success">Mark Tasks As Complete</button>
                        @endcan
                            <a href="#" data-bs-toggle="modal" data-bs-target="#createTaskModal" class="btn btn-primary"
                            data-status="to_do">New Task</a>
                    </div>
                </div>
            </div>
        </div>
        @session('success')
            <div class="alert alert-success" role="alert">
                {{ $value }}
            </div>
        @endsession
        @if (request('filter'))
            <form class="card" id="filter">
                @if ($project)
                    <input type="hidden" value="{{ $project->id }}" name="project_id">
                @endif
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-3 col-sm-6">
                            <label for="search" class="form-label">Search</label>
                            <input id="search" type="text" name="search" class="form-control mb-sm-0 mb-2"
                                placeholder="Search" value="{{ request('search') }}">
                        </div>
                        <div class="col-12 col-md-3 col-sm-6">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">-- Status --</option>
                                @foreach (config('site.tasks.status') as $key => $value)
                                    <option value="{{ $key }}" @selected(request('status') == $key)>
                                        {{ $value }}
                                    </option>
                                @endforeach

                            </select>
                        </div>
                        <div class="col-12 col-md-3 col-sm-6">
                            <label for="user" class="form-label">Assigned To</label>
                            <select name="user" id="user" class="form-select select2">
                                <option value="">--Select Employee--</option>
                                @foreach ($project ? $project->users : $users as $key => $user)
                                    <option value="{{ $user->id }}" @selected(request('user') == $user->id)>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3 col-sm-6">
                            <label for="cdate" class="form-label">Created Date</label>
                            <input id="cdate" type="date" name="cdate" class="form-control mb-sm-0 mb-2"
                                value="{{ request('cdate') }}">
                        </div>
                        <div class="col-12 col-md-3 col-sm-6">
                            <label for="duedate" class="form-label">Due Date</label>
                            <input id="duedate" type="date" name="duedate" class="form-control mb-sm-0 mb-2"
                                value="{{ request('duedate') }}">
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end ">
                    <button type="submit" class="btn btn-dark  px-3" name="filter" value="1">
                        <i class="fas fa-save"></i> Submit
                    </button>
                    <a href="{{ route('tasks.index', (isset($project) ? ['project_id' => $project->id] : [])) }}" class="btn btn-danger ms-2  px-3">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        @endif
        <table class="table table-bordered">
            <tr>
                @can('mark-task-complete')
                    <th><input class="maincheck" onclick="checkAll()" type="checkbox"></th>
                @endcan
                <th>No</th>
                <th>Title</th>
                <th>Project</th>
                <th>Due Date</th>
                <th>Assigned To</th>
                <th>Time </th>
                <th width="80px">Status</th>
            </tr>
            @foreach ($tasks as $key => $task)
                <tr class="{{ $task->color }}">
                    @can('mark-task-complete')
                        <td><input type="checkbox" class="check" name="status[]" value="{{ $task->id }}"></td>
                    @endcan
                    <td>{{ $loop->iteration }}</td>
                    <td> <a href="{{ route('tasks.show', $task->id) }}"> {{ $task->title }} </a></td>
                    <td>{{ $task->project?->name }}</td>
                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($task->due_date)->format('j F Y') }}</td>
                    <td class="text-nowrap">{{ $task->user->name }}</td>
                    <td class="text-nowrap">{{ $task->getTotalElapsedTime() }}</td>
                    <td>
                        <div class="dropdown">
                            @if (auth()->id() == $task->user_id || Auth::user()->hasRole('Admin'))
                                <button class="btn dropdown-toggle f-13" type="button" id="dropdownMenuButton1"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    {!! $task->status_badge !!}
                                </button>

                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    @foreach (config('site.tasks.status') as $key => $value)
                                        {{-- @unless (in_array($key, $hiddenStatuses)) --}}
                                            <li>
                                                <form action="{{ route('task.updateStatus', $task->id) }}" method="POST"
                                                    style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ $key }}">
                                                    <button type="submit" class="dropdown-item">{{ $value }}</button>
                                                </form>
                                            </li>
                                        {{-- @endunless --}}
                                    @endforeach

                                </ul>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>

        {!! $tasks->links('pagination::bootstrap-5') !!}

        <x-task-modal :project="$project" />
    </div>

    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('tasks.export') }}" method="POST">
                    @csrf
                    @if ($project)
                    <input type="hidden" value="{{ $project->id }}" name="project_id">
                @endif
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportModalLabel">Export Tasks</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="from_date" class="form-label">From Date</label>
                        <input id="from_date" class="form-control" type="date" name="from_date" required>
                        <label for="to_date" class="form-label">To Date</label>
                        <input id="to_date" class="form-control" type="date" name="to_date" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Download</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @can('mark-task-complete')
        @push('scripts')
            <script>
                $(document).ready(function() {

                    $('.check').on('change', function() {
                        updateSubmitButtonState();
                    });

                    window.checkAll = function() {
                        var isChecked = $('.maincheck').prop('checked');
                        $('.check').prop('checked', isChecked);
                        updateSubmitButtonState();
                    }

                    function updateSubmitButtonState() {
                        if ($('.check:checked').length > 0) {
                            $('#markCompleteBtn').prop('disabled', false);
                        } else {
                            $('#markCompleteBtn').prop('disabled', true);
                        }
                    }
                });

                function submitCompletedTasks() {
                    var selectedTasks = [];

                    $('.check:checked').each(function() {
                        selectedTasks.push($(this).val());
                    });

                    if (selectedTasks.length === 0) {
                        alert('Please select at least one task.');
                        return;
                    }

                    $.ajax({
                        url: "{{ route('task.mark-complete') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            task_ids: selectedTasks,
                        },
                        success: function(response) {

                            alert('Tasks marked as complete successfully!');
                            location.reload();
                        },
                        error: function(xhr) {

                            console.error('Error marking tasks complete:', xhr.responseText);
                        }
                    });
                }
            </script>
        @endpush
    @endcan
@endsection
