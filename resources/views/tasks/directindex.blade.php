@extends('layouts.app')
@section('title')
    {{-- {{ $project->name }} --}}
@endsection
@section('content')
    <div class="container">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                <h2>My Tasks</h2>
                <div>
                    <a class="btn btn-success" href="{{ route('tasks.directindex', ['filter' => 1]) }}">Filters</a>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#createTaskModal" class="btn btn-primary"
                        data-status="to_do">Create New Task</a>
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
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-4 col-sm-6">
                            <input type="text" name="search" class="form-control mb-sm-0 mb-2" placeholder="Search"
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-12 col-md-4 col-sm-6">

                            <select name="status" id="status" class="form-select">
                                <option value="" selected="">-- Select --</option>
                                <option {{ request('status') == 'to_do' ? 'selected' : '' }} value="to_do" >To Do</option>
                                <option {{ request('status') == 'in_progress' ? 'selected' : '' }} value="in_progress">In
                                    Progress</option>
                                <option {{ request('status') == 'in_review' ? 'selected' : '' }} value="in_review">In
                                    Review</option>
                                <option {{ request('status') == 'blocked' ? 'selected' : '' }} value="blocked">Blocked
                                </option>
                                <option {{ request('status') == 'closed' ? 'selected' : '' }}  value="closed">Closed
                                </option>
                                <option {{ request('status') == 'closed' ? 'completed' : '' }}  value="completed">
                                    Completed</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 col-sm-6">

                            <select name="project" id="project" class="form-select">
                                <option value="" >-- Select --</option>
                                @foreach ($projects as $project)
                                    <option  {{ request('project') == $project->id ? 'selected' : '' }} value="{{ $project->id }}">
                                        {{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end ">
                    <button type="submit" class="btn btn-dark  px-3" name="filter" value="1">
                        <i class="fas fa-save"></i> Submit
                    </button>
                    <a href="{{ route('tasks.directindex') }}" class="btn btn-danger ms-2  px-3">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        @endif
        <table class="table table-bordered">
            <tr>
                <th>No</th>
                <th>Title</th>
                <th>Description</th>
                <th>Due Date</th>
                <th>Project</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Time </th>
                <th width="280px">Action</th>
            </tr>
            @foreach ($tasks as $key => $task)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td> {{ $task->title }} </td>
                    <td>{{ $task->description }}</td>
                    <td>{{ $task->due_date }}</td>
                    <td>{{ $task->project->name }}</td>
                    <td>{{ $task->user->name }}</td>
                    <td>{!! $task->status_badge !!}</td>
                    <td>{{ $task->getTotalElapsedTime(auth()->id()) }}</td>
                    <td>
                        <a class="btn btn-info btn-sm" href="{{ route('tasks.show', $task->id) }}"><i
                                class="fa-solid fa-list"></i> Show</a>
                        {{-- <a class="btn btn-primary btn-sm" href="{{ route('users.edit',$user->id) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a> --}}
                        <form method="POST" action="{{ route('tasks.destroy', $task->id) }}" style="display:inline">
                            @csrf
                            @method('DELETE')

                            <button onclick="return confirm('Are you sure you want to delete this task?')" type="submit"
                                class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>

        {!! $tasks->links('pagination::bootstrap-5') !!}

        <!-- Create Task Modal -->
        <div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('tasks.directstore') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="createTaskModalLabel">Create Task</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="project" class="form-label">Project</label>
                                <select name="project_id" id="project" class="form-select">
                                    <option value="">Select a project</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" name="title" id="title" class="form-control" required>
                                @error('title')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control"></textarea>
                                @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" value="{{ date('Y-m-d') }}" name="due_date" id="due_date" class="form-control">
                                @error('due_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select name="priority" id="priority" class="form-select" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                                @error('priority')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="user_id" class="form-label">Select User</label>
                                <select name="user_id" id="user_id" class="form-select">
                                    <option value="">Select a user</option>
                                </select>
                            </div>
                            <input type="hidden" name="status" id="task_status">

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Create Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const kanbanItems = document.querySelectorAll('.kanban-item');
            const kanbanLists = document.querySelectorAll('.kanban-list');
            const createTaskModal = document.getElementById('createTaskModal');
            const taskStatusInput = document.getElementById('task_status');

            createTaskModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var status = button.getAttribute('data-status');
                taskStatusInput.value = status;
            });

            kanbanItems.forEach(item => {
                item.addEventListener('dragstart', handleDragStart);
                item.addEventListener('dragend', handleDragEnd);
            });

            kanbanLists.forEach(list => {
                list.addEventListener('dragover', handleDragOver);
                list.addEventListener('drop', handleDrop);
            });

            function handleDragStart(e) {
                e.dataTransfer.setData('text/plain', e.target.dataset.id);
                setTimeout(() => {
                    e.target.classList.add('invisible');
                }, 0);
            }

            function handleDragEnd(e) {
                e.target.classList.remove('invisible');
            }

            function handleDragOver(e) {
                e.preventDefault();
            }

            function handleDrop(e) {
                e.preventDefault();
                const id = e.dataTransfer.getData('text');
                const draggableElement = document.querySelector(`.kanban-item[data-id='${id}']`);
                const dropzone = e.target.closest('.kanban-list');
                dropzone.appendChild(draggableElement);

                const status = dropzone.id;

                updateTaskStatus(id, status);
            }

            function updateTaskStatus(id, status) {
                fetch(`/tasks/${id}/update-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status
                    })
                }).then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to update task status');
                    }
                    return response.json();
                }).then(data => {
                    console.log('Task status updated:', data);
                }).catch(error => {
                    console.error('Error:', error);
                });
            }
        });

        $(document).ready(function() {
            $('#project').change(function() {
                var projectId = $(this).val();

                if (projectId) {
                    $.ajax({
                        url: '{{ route('clients.getUsers', ':id') }}'.replace(':id', projectId),
                        type: 'GET',
                        success: function(data) {
                            $('#user_id').empty();
                            $('#user_id').append('<option value="">Select a user</option>');

                            $.each(data, function(key, user) {
                                $('#user_id').append('<option value="' + user.id +
                                    '">' + user.name + '</option>');
                            });
                        },
                        error: function(xhr) {
                            alert('Error fetching users');
                        }
                    });
                } else {
                    $('#user_id').empty();
                    $('#user_id').append('<option value="">Select a user</option>');
                }
            });
        });
    </script>
@endsection
