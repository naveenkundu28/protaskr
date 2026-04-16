@extends('layouts.app')
@section('title')
    {{ $task->title }} - Task Details
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                <h3 class="w-90">{{ $task->title }} - Task Details </h3>
                <div>
                    @if (auth()->id() == $task->user_id || Auth::user()->hasRole('Admin'))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editTaskModal">
                            <i class="bi bi-pencil-square"></i> </button>
                    @endif
                    <a href="{{ route('clients.tasks.index', $task->project->id) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-90deg-left"></i> </a>
                </div>
            </div>
        </div>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="alert alert-warning alert-dismissible fade show" role="alert" id="jsresp" style="display: none">
            <span id="textpara"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="card-title">{{ $task->title }}</h5>

                                <div class="d-flex justify-content-between">
                                    <p class="card-text"><strong class="f-14">Due Date:</strong><br> {{ $task->due_date }}
                                    </p>
                                    <p class="card-text w90"><strong class="f-14">Priority:</strong><br> <span
                                            class="badge {{ $task->priority == 'low' ? 'bg-success' : ($task->priority == 'medium' ? 'bg-warning' : 'bg-danger') }}">{{ ucfirst($task->priority) }}</span>
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between">

                                    <p class="card-text "><strong class="f-14">Assign To:</strong><br>
                                        {{ $task->user->name }}</p>
                                    <p class="card-text w90"><strong class="f-14">Status:</strong><br>
                                        {!! $task->status_badge !!}
                                    </p>
                                </div>
                                <p class="card-text">{!! $task->description !!}</p>
                            </div>

                            <div class="col-md-6 border-start">
                                <div id="time-tracker">
                                    <div class="d-flex justify-content-start ">
                                        <h5 class="me-3">Time Tracker </h5>
                                        <p class="fw-bold f-16">( <span id="time-display"> 00:00:00 </span> )</p>
                                    </div>

                                    <div class=" d-flex justify-content-between">
                                        <div class="d-flex">
                                            @if (auth()->id() == $task->user_id || Auth::user()->hasRole('Admin'))
                                                <button id="start-btn" onclick="start()" class="btn btn-success btn-sm"><i
                                                        class="bi bi-play-fill"></i></button>
                                                {{-- <button id="pause-btn" class="btn btn-warning btn-sm"><i
                                                class="bi bi-pause-fill"></i></button> --}}
                                                <button id="stop-btn" class="btn btn-danger btn-sm "
                                                    style="display: none"><i class="bi bi-stop-fill"></i></button>
                                                @if ($task->user->manual == true)
                                                    <div class="ms-2">

                                                        <button data-bs-toggle="modal" data-bs-target="#manualtrack"
                                                            class="btn btn-primary">Track Manually</button>

                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                        <div>
                                            <p id="tracked_hours" class="fw-bold m-0">Total time: {{ $totalTime }} </p>
                                        </div>
                                    </div>
                                </div>


                                <div class="mt-2">
                                    <p>Chats</p>
                                    @foreach ($chats as $chat)
                                        <div class="card-body border p-1 mb-2">
                                            <small>{{ $chat->user->name }}</small>
                                            <p class="m-0">{!! $chat->message !!}</p>
                                            <small>{{ date('d-m-Y', strtotime($chat->created_at)) }}</small>
                                        </div>
                                    @endforeach
                                    <form id="chatForm" method="POST" action="{{ route('chat.send', $task->id) }}">
                                        @csrf
                                        {{-- <textarea class="form-control" name="message" required></textarea> --}}
                                        <textarea id="editor" class="form-control" name="message"></textarea>
                                        <button class="btn btn-primary mt-2 float-end" type="submit">Send</button>

                                        @error('message')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </form>


                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                    <h5>Uploads</h5>
                                    <a target="_blank" href="{{ route('task.files.create', $task->id) }}"
                                        class="btn btn-primary"><i class="bi bi-plus-circle"></i></a>
                                </div>

                                <!-- Checklist items -->
                                <div class="list-group mt-2" id="checklist-items">
                                    <div class="row gap-2">
                                        @foreach ($files as $file)
                                            <div
                                                class=" col-md-auto mb-4 list-group-item d-flex justify-content-between align-items-center">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h5 class="card-title">{{ $file->name }}</h5>
                                                        <p class="card-text"><strong>Type:</strong> {{ $file->type }}</p>
                                                        <a href="{{ route('task.files.show', [$task->id, $file->id]) }}"
                                                            target="_blank" class="btn btn-primary"> <i
                                                                class="bi bi-eye"></i> </a>
                                                        <a href="{{ route('task.files.download', [$task->id, $file->id]) }}"
                                                            class="btn btn-info text-white"> <i class="bi bi-download"></i>
                                                        </a>
                                                        @if (auth()->id() == $task->user_id || Auth::user()->hasRole('Admin'))
                                                            <a href="{{ route('task.files.edit', [$task->id, $file->id]) }}"
                                                                class="btn btn-warning"> <i class="bi bi-pencil-square"></i>
                                                            </a>
                                                            <form
                                                                action="{{ route('task.files.destroy', [$task->id, $file->id]) }}"
                                                                method="POST" class="d-inline"
                                                                onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger"> <i
                                                                        class="bi bi-trash"></i> </button>
                                                            </form>
                                                        @endif
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
                <table class="table table-bordered">
                    <tr>
                        <th>No</th>
                        <th>Start time</th>
                        <th>End Time</th>
                        <th>Total Time</th>
                    </tr>
                    @foreach ($taskHistories as $key => $taskHistory)
                        <tr>
                            <td data-id="{{ $taskHistory->id }}">{{ $loop->iteration }}</td>
                            <td> {{ $taskHistory->start_time }} </td>
                            <td>{{ $taskHistory->end_time }}</td>
                            {{-- <td>{{ $taskHistory->elapsed_time_formatted }}</td> --}}
                            <td data-id="{{ $taskHistory->id }}">

                                {{-- Display Mode --}}
                                <span class="time-text">
                                    {{ $taskHistory->elapsed_time_formatted }}
                                </span>

                                @if (Auth::user()->hasRole('Admin'))
                                    <button class="btn btn-sm btn-primary edit-time ms-2">
                                        <i class="bi bi-pencil"></i>
                                    </button>


                                    {{-- Edit Mode --}}
                                    <div class="align-items-center d-flex gap-2 mt-1 time-edit d-none">
                                        <div>
                                            <input type="text" class="form-control form-control-sm time-input"
                                                value="{{ $taskHistory->elapsed_time_formatted }}"
                                                placeholder="HH:MM:SS">
                                        </div>

                                        <div class="mt-1">
                                            <button class="btn btn-success btn-sm save-time">
                                                <i class="bi bi-check"></i>
                                            </button>
                                            <button class="btn btn-secondary btn-sm cancel-time">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <!-- Add Checklist Modal -->
        <div class="modal fade" id="addChecklistModal" tabindex="-1" aria-labelledby="addChecklistModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="add-checklist-form">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addChecklistModalLabel">Add Checklist Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="checklist-name" class="form-label">Item Name</label>
                                <input type="text" name="name" id="checklist-name" class="form-control" required>
                                <div class="invalid-feedback" id="checklist-name-error"></div>
                            </div>
                            <input type="hidden" name="task_id" id="task_id" value="{{ $task->id }}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if (auth()->id() == $task->user_id || Auth::user()->hasRole('Admin'))
            <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" name="title" id="title" class="form-control"
                                        value="{{ $task->title }}" required>
                                    @error('title')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" id="summernote" class="form-control">{{ $task->description }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" name="due_date" id="due_date" class="form-control"
                                        value="{{ $task->due_date }}">
                                    @error('due_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select name="priority" id="priority" class="form-select" required>
                                        <option value="low" {{ $task->priority == 'low' ? 'selected' : '' }}>Low
                                        </option>
                                        <option value="medium" {{ $task->priority == 'medium' ? 'selected' : '' }}>Medium
                                        </option>
                                        <option value="high" {{ $task->priority == 'high' ? 'selected' : '' }}>High
                                        </option>
                                    </select>
                                    @error('priority')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select" required>
                                        @foreach (config('site.tasks.status') as $key => $value)
                                            @unless (in_array($key, $hiddenStatuses))
                                                <option value="{{ $key }}" @selected($task->status == $key)>
                                                    {{ $value }}
                                                </option>
                                            @endunless
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Assign To</label>
                                    <select name="user_id" id="user_id" class="form-select" required>
                                        <option value="">Select a user</option>
                                        @foreach ($task->project?->sortedUsers() as $user)
                                            <option value="{{ $user->id }}" @selected($user->id == $task->user_id)>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Update Task</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
        @if ($task->user->manual == true)
            {{-- manual track modal --}}

            <div class="modal fade" id="manualtrack" tabindex="-1" aria-labelledby="manualtrackLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('tasks.manual', $task->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title" id="manualtrackLabel">Track Time Manually</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="start" class="form-label">Start Time</label>
                                    <input type="datetime-local" name="start" id="start" class="form-control"
                                        value="">
                                    @error('start')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="end" class="form-label">End Time</label>
                                    <input type="datetime-local" name="end" id="end" class="form-control"
                                        value="">
                                    @error('end')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @push('scripts')
        <script>
            let config = {};
            config.toolbar = "basic";
            let editor = new RichTextEditor("#editor", config);

            $('#chatForm').on('submit', function() {
                $('#editor').val(editor.getHTMLCode());
            });

            function start() {
                console.log('inhere');
            }
            let timer;
            let seconds = {{ $task->elapsed_time }};
            let isRunning = {{ $task->is_running ? 'true' : 'false' }};

            function formatTime(sec) {
                let hours = Math.floor(sec / 3600);
                let minutes = Math.floor((sec % 3600) / 60);
                let seconds = sec % 60;
                return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }

            function updateTimeDisplay() {
                $('#time-display').text(formatTime(seconds));
            }

            function saveTime() {
                $.ajax({
                    url: '{{ route('task.save-time', ['task' => $task->id]) }}',
                    method: 'POST',
                    data: {
                        elapsed_time: seconds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('Time saved successfully.');
                        seconds = response.elapsed_time;
                    },
                    error: function(error) {
                        console.error('Error saving time:', error);
                    }
                });
            }

            if (isRunning) {
                $('#start-btn').hide();
                $('#stop-btn').show();
                timer = setInterval(() => {
                    seconds++;
                    updateTimeDisplay();
                    if (seconds % 10 === 0) {
                        saveTime();
                    }

                }, 1000);
            }

            $('#start-btn').on('click', function() {
                console.log('here');
                if (!isRunning) {
                    $.ajax({
                        url: '{{ route('task.start', ['taskid' => $task->id]) }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status == false) {
                                isRunning = false;
                                $('#textpara').text(response.message);
                                $('#jsresp').show();
                                console.log(response.message);
                            } else {
                                isRunning = true;

                                timer = setInterval(() => {
                                    seconds++;
                                    updateTimeDisplay();
                                    if (seconds % 10 === 0) {
                                        saveTime();
                                    }
                                }, 1000);
                                $('#start-btn i').removeClass('bi bi-play-fill');
                                $('#start-btn i').addClass('spinner-border spinner-border-sm');
                                setTimeout(function() {
                                    $('#start-btn').hide();
                                    $('#stop-btn').show();
                                }, 1000);

                            }
                        },
                        error: function(error) {
                            console.error('Error starting timer:', error);
                        }
                    });
                }
            });


            $('#pause-btn').on('click', function() {
                if (isRunning) {
                    $.ajax({
                        url: '{{ route('task.pause', ['taskid' => $task->id]) }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            console.log('Timer paused.');
                        },
                        error: function(error) {
                            console.error('Error pausing timer:', error);
                        }
                    });

                    isRunning = false;
                    clearInterval(timer);
                }
            });

            $('#stop-btn').on('click', function() {
                $.ajax({
                    url: '{{ route('task.stop', ['taskid' => $task->id]) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        //console.log('Timer stop.');
                        $('#tracked_hours').text('Total time: ' + response.total_time)
                        $('#start-btn i').addClass('bi bi-play-fill');
                        $('#start-btn i').removeClass('spinner-border spinner-border-sm');

                        $('#stop-btn i').removeClass('bi bi-stop-fill');
                        $('#stop-btn i').addClass('spinner-border spinner-border-sm');
                        setTimeout(function() {
                            $('#stop-btn i').removeClass('spinner-border spinner-border-sm');
                            $('#stop-btn i').addClass('bi bi-stop-fill');
                            $('#start-btn').show();
                            $('#stop-btn').hide();
                        }, 1000);

                    },
                    error: function(error) {
                        console.error('Error stoping timer:', error);
                    }
                });

                isRunning = false;
                clearInterval(timer);
                seconds = 0;
                // saveTime();
                updateTimeDisplay();
            });

            updateTimeDisplay();




            // ------------------inline edit section js for admin
            // Convert HH:MM:SS → seconds
            function timeToSeconds(time) {
                let parts = time.split(':').map(p => parseInt(p) || 0);

                let h = 0,
                    m = 0,
                    s = 0;

                if (parts.length === 3) {
                    [h, m, s] = parts;
                } else if (parts.length === 2) {
                    [h, m] = parts; // treat as HH:MM
                } else if (parts.length === 1) {
                    h = parts[0];
                }

                return (h * 3600) + (m * 60) + s;
            }

            // Convert seconds → HH:MM:SS
            function secondsToTime(sec) {
                let h = Math.floor(sec / 3600);
                let m = Math.floor((sec % 3600) / 60);
                let s = sec % 60;

                return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            }


            // Edit Click
            $(document).on('click', '.edit-time', function() {
                let td = $(this).closest('td');

                td.find('.time-text, .edit-time').hide();
                td.find('.time-edit').removeClass('d-none');
            });

            // Cancel Click
            $(document).on('click', '.cancel-time', function() {
                let td = $(this).closest('td');

                td.find('.time-edit').addClass('d-none');
                td.find('.time-text, .edit-time').show();
            });

            // Save Click
            $(document).on('click', '.save-time', function() {
                let td = $(this).closest('td');
                let id = td.data('id');
                let inputVal = td.find('.time-input').val();

                let tracker_seconds = timeToSeconds(inputVal);

                let url = "{{ route('tasks.updateTime', ':id') }}";
                url = url.replace(':id', id);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        elapsed_time: tracker_seconds,
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT'
                    },
                    success: function(res) {
                        if (res.success) {
                            console.log('res', res.success);

                            td.find('.time-text').text(secondsToTime(tracker_seconds));

                            td.find('.time-edit').addClass('d-none');
                            td.find('.time-text, .edit-time').show();
                        }
                    },
                    error: function(error) {
                        console.log('Something went wrong', error);
                    }
                });
            });
        </script>
    @endpush
@endsection
