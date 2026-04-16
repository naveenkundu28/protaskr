<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createTaskModalLabel">Create Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="project" class="form-label">Project</label>
                        <select name="client_id" id="project" class="form-select">
                            <option value="">Select a project</option>
                            @foreach ($inProgressProjects as $thisProject)
                                <option value="{{ $thisProject->id }}" @selected($thisProject->id == $project?->id)>
                                    {{ $thisProject->name }}
                                </option>
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
                        <textarea name="description" id="summernote" class="form-control"></textarea>
                        @error('description')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" value="{{ date('Y-m-d') }}" name="due_date" id="due_date"
                            class="form-control">
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
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            @foreach (config('site.tasks.status') as $key => $value)
                                @unless (in_array($key, $hiddenStatuses))
                                    <option value="{{ $key }}">
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
                        <label for="user_id" class="form-label">Select User</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">Select a user</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>


    <script>
       $('#summernote').summernote({
        placeholder: 'Task Description',
        tabsize: 2,
        height: 120,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'clear']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link']],
          ['view', []]
        ]
      });
        $(document).ready(function() {
            // const taskStatusInput = document.getElementById('task_status');
            const createTaskModal = document.getElementById('createTaskModal');
            createTaskModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var status = button.getAttribute('data-status');
                // taskStatusInput.value = status;
            });

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
@endpush
