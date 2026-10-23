@extends('layouts.app')

@section('title', isset($project) ? $project->name . ' - Tasks' : 'All Tasks')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/project-module.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/tasks.css') }}?v={{ filemtime(public_path('assets/tasks.css')) }}">
@endpush

@push('scripts')
<script>
    jQuery(function ($) {
        const loadTaskResults = async (url) => {
            const current = document.querySelector('.tasks-page');
            if (!current) return;
            current.classList.add('is-loading');
            try {
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Unable to load tasks');
                const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                const next = doc.querySelector('.tasks-page');
                if (!next) throw new Error('Task results are missing');
                current.replaceWith(next);
                history.pushState({}, '', url);
                $(next).find('.select2').select2({ width: '100%', allowClear: true });
            } catch (error) { window.location.assign(url); }
        };
        $(document).on('click', '.tasks-page .pm-tabs a', function (event) {
            if (this.href.includes('tab=completed')) return;
            event.preventDefault();
            loadTaskResults(this.href);
        });
        $(document).on('change', '#tasksFilterForm select, #tasksFilterForm input[type="date"]', function () {
            const form = document.getElementById('tasksFilterForm');
            form.querySelector('[name="page"]')?.remove();
            loadTaskResults(form.action + '?' + new URLSearchParams(new FormData(form)).toString());
        });
    });
</script>
@endpush

@section('content')
<div class="container-fluid mt-80 tasks-page">
    {{-- Alerts --}}

    @php
        $taskListFilters = array_filter([
            'tab' => $currentTab,
            'status' => request('status'),
            'user' => request('user'),
            'duedate' => request('duedate'),
            'client_id' => request('client_id'),
            'delivery_project_id' => request('delivery_project_id'),
            'project_id' => optional($project)->id,
        ], fn ($value) => $value !== null && $value !== '');
        $withoutTaskFilter = function ($key) use ($taskListFilters) {
            $filters = $taskListFilters;
            unset($filters[$key]);
            return $filters;
        };
    @endphp

    {{-- Page title and actions --}}
    <div class="pm-heading">
        <div class="inner_page_topbar">
            <div class="tasks-page-title">
                <h1>{{ isset($project) ? $project->name . ' - Tasks' : 'Tasks' }}</h1>
            </div>
            <div class="right_search_box">
                {{-- Export Button --}}
                @can('tasks-export')
                    <button type="button" class="btn btn-outline-secondary pm-button" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="bi bi-download"></i> Export
                    </button>
                @endcan

                {{-- Bulk Complete Button --}}
                @can('tasks-bulk-complete')
                    <button id="markCompleteBtn" onclick="submitCompletedTasks()" disabled type="button" class="btn btn-success pm-button">
                        <i class="bi bi-check-all"></i> Mark Complete
                    </button>
                @endcan

            </div>
        </div>
    </div>

    {{-- Always-visible task filters --}}
    @php
        $hasFilters = request('status') || request('user') || request('duedate') || request('client_id') || request('delivery_project_id');
    @endphp
    <div class="tasks-filter-fields mb-3">
        <form method="GET" action="{{ route('tasks.index') }}" id="tasksFilterForm" class="m-0">
            @if ($project)
                <input type="hidden" value="{{ $project->id }}" name="project_id">
            @endif
            <input type="hidden" name="tab" value="{{ $currentTab }}">

            <div class="row g-3 align-items-end">
                    {{-- Status Filter --}}
                    <div class="col-lg-2 col-md-6">
                        <label class="pm-filter-label" for="filter_status">Status</label>
                        <select name="status" id="filter_status" class="form-select pm-filter-input" aria-label="Filter by status">
                            <option value="">All Statuses</option>
                            @foreach (config('site.tasks.status') as $key => $value)
                                @if ($key != 'closed')
                                    <option value="{{ $key }}" @selected(request('status') == $key)>
                                        {{ $value }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Assigned To Filter --}}
                    <div class="col-lg-2 col-md-6">
                        <label class="pm-filter-label" for="filter_user">Assigned To</label>
                        <select name="user" id="filter_user" class="form-select pm-filter-input select2" aria-label="Filter by user">
                            <option value="">All Employees</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(request('user') == $user->id)>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Client Filter --}}
                    <div class="col-lg-2 col-md-6">
                        <label class="pm-filter-label" for="filter_client">Client</label>
                        <select name="client_id" id="filter_client" class="form-select pm-filter-input select2"
                            aria-label="Filter by client" @disabled($project)>
                            <option value="">All Clients</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected((string) (optional($project)->id ?: request('client_id')) === (string) $client->id)>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Project Filter --}}
                    <div class="col-lg-2 col-md-6">
                        <label class="pm-filter-label" for="filter_project">Project</label>
                        <select name="delivery_project_id" id="filter_project" class="form-select pm-filter-input select2"
                            aria-label="Filter by project">
                            <option value="">All Projects</option>
                            @foreach ($filterProjects as $filterProject)
                                <option value="{{ $filterProject['id'] }}" @selected(request('delivery_project_id') == $filterProject['id'])>
                                    {{ $filterProject['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Due Date --}}
                    <div class="col-lg-2 col-md-6">
                        <label class="pm-filter-label" for="filter_duedate">Due Date</label>
                        <input id="filter_duedate" type="date" name="duedate" class="form-control pm-filter-input"
                            value="{{ request('duedate') }}">
                    </div>

                    {{-- Action Buttons --}}
                    <div class="col-lg-2 col-md-6 tasks-filter-actions">
                        @if ($hasFilters)
                            <a href="{{ route('tasks.index', array_filter(['tab' => $currentTab, 'project_id' => optional($project)->id])) }}" class="tasks-filter-clear" title="Clear filters" aria-label="Clear filters">
                                <i class="bi bi-x-lg" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>
            </div>
        </form>
    </div>

    {{-- Task status tabs --}}
    <div class="pm-tabs">
        @php
            $tabList = [
                'all' => 'All Tasks' . (isset($tabCounts['all']) ? ' (' . $tabCounts['all'] . ')' : ''),
                'to_do' => 'To Do' . (isset($tabCounts['to_do']) ? ' (' . $tabCounts['to_do'] . ')' : ''),
                'in_progress' => 'In Progress' . (isset($tabCounts['in_progress']) ? ' (' . $tabCounts['in_progress'] . ')' : ''),
                'overdue' => 'Overdue' . (isset($tabCounts['overdue']) ? ' (' . $tabCounts['overdue'] . ')' : ''),
                'in_review' => 'In Review' . (isset($tabCounts['in_review']) ? ' (' . $tabCounts['in_review'] . ')' : ''),
                'on_hold' => 'On Hold' . (isset($tabCounts['on_hold']) ? ' (' . $tabCounts['on_hold'] . ')' : ''),
                'completed' => 'Completed' . (isset($tabCounts['completed']) ? ' (' . $tabCounts['completed'] . ')' : ''),
            ];
        @endphp
        @foreach ($tabList as $tabKey => $tabLabel)
            <a class="task-tab-{{ $tabKey }} {{ $currentTab == $tabKey ? 'active' : '' }}"
                href="{{ route('tasks.index', array_merge($withoutTaskFilter('status'), ['tab' => $tabKey])) }}">
                {{ $tabLabel }}
            </a>
        @endforeach
    </div>

    {{-- Active filter tags --}}
    @php
        $selectedUser = request('user') ? $users->firstWhere('id', request('user')) : null;
        $selectedClient = request('client_id') ? $clients->firstWhere('id', request('client_id')) : null;
        $selectedFilterProject = request('delivery_project_id') ? $filterProjects->firstWhere('id', (string) request('delivery_project_id')) : null;
        $statusConfig = config('site.tasks.status');
        $selectedStatusLabel = request('status') && isset($statusConfig[request('status')]) ? $statusConfig[request('status')] : null;
    @endphp
    @if (request('status') || request('user') || request('duedate') || request('client_id') || request('delivery_project_id'))
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <span class="text-muted small">Active Filters:</span>
            @if ($selectedStatusLabel)
                <span class="badge bg-light text-primary border py-1 px-2 d-inline-flex align-items-center gap-1">
                    Status: {{ $selectedStatusLabel }}
                    <a href="{{ route('tasks.index', $withoutTaskFilter('status')) }}" class="text-primary text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            @if ($selectedUser)
                <span class="badge bg-light text-primary border py-1 px-2 d-inline-flex align-items-center gap-1">
                    Assigned to: {{ $selectedUser->name }}
                    <a href="{{ route('tasks.index', $withoutTaskFilter('user')) }}" class="text-primary text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            @if ($selectedClient)
                <span class="badge bg-light text-primary border py-1 px-2 d-inline-flex align-items-center gap-1">
                    Client: {{ $selectedClient->name }}
                    <a href="{{ route('tasks.index', $withoutTaskFilter('client_id')) }}" class="text-primary text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            @if ($selectedFilterProject)
                <span class="badge bg-light text-primary border py-1 px-2 d-inline-flex align-items-center gap-1">
                    Project: {{ $selectedFilterProject['name'] }}
                    <a href="{{ route('tasks.index', $withoutTaskFilter('delivery_project_id')) }}" class="text-primary text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            @if (request('duedate'))
                <span class="badge bg-light text-primary border py-1 px-2 d-inline-flex align-items-center gap-1">
                    Due: {{ \Carbon\Carbon::parse(request('duedate'))->format('d M Y') }}
                    <a href="{{ route('tasks.index', $withoutTaskFilter('duedate')) }}" class="text-primary text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            <a href="{{ route('tasks.index', array_filter(['tab' => $currentTab, 'project_id' => optional($project)->id])) }}" class="btn btn-link btn-sm text-danger p-0 text-decoration-none small ms-1">
                <i class="bi bi-x-circle"></i> Clear All
            </a>
        </div>
    @endif

    {{-- ================= TABLE CARD ================= --}}
    <div class="pm-card tasks-table-card">
        <div class="table-responsive project_table">
            <table class="table pm-table tasks-table align-middle">
                <thead>
                    <tr>
                        @can('tasks-bulk-complete')
                            <th class="tasks-select-column"><input class="maincheck form-check-input" onclick="checkAll()" type="checkbox" aria-label="Select all available tasks"></th>
                        @endcan
                        <th class="tasks-number-column">#</th>
                        <th>Task</th>
                        <th>Story</th>
                        <th>Project</th>
                        <th>Client</th>
                        <th>Assigned To</th>
                        <th>Due Date</th>
                        <th>Time Tracked</th>
                        <th class="tasks-status-column">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr class="{{ $task->color }}">
                            @can('tasks-bulk-complete')
                                <td class="tasks-select-column">
                                    @if ($task->status !== 'completed' && $task->story?->status !== 'completed')
                                        <input type="checkbox" class="check form-check-input" name="status[]" value="{{ $task->id }}">
                                    @else
                                        <i class="bi bi-lock-fill text-muted" title="{{ $task->status === 'completed' ? 'This task is completed' : 'This task belongs to a completed story' }}" aria-label="Locked task"></i>
                                    @endif
                                </td>
                            @endcan
                            <td class="tasks-number-column">{{ $tasks->firstItem() + $loop->index }}</td>
                            <td class="tasks-title-cell">
                                <a class="pm-link fw-semibold" href="{{ route('tasks.show', $task->id) }}">
                                    {{ $task->title }}
                                </a>
                                @if ($task->description)
                                    <small>{{ \Illuminate\Support\Str::limit(strip_tags($task->description), 100) }}</small>
                                @endif
                            </td>
                            <td class="tasks-related-cell">
                                @if ($task->story)
                                    <a href="{{ route('stories.show', $task->story) }}" class="tasks-related-link">{{ $task->story->title }}</a>
                                @else
                                    <span class="tasks-muted">—</span>
                                @endif
                            </td>
                            <td class="tasks-related-cell">
                                @if ($task->deliveryProject)
                                    <a href="{{ route('projects.show', $task->deliveryProject) }}" class="tasks-related-link">{{ $task->deliveryProject->name }}</a>
                                @else
                                    <span class="tasks-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $task->project?->name ?: ($task->deliveryProject?->client?->name ?: '—') }}</td>
                            <td class="text-nowrap">{{ $task->user->name ?? '—' }}</td>
                            <td class="text-nowrap tasks-date-cell">
                                @if ($task->due_date)
                                    <span class="tasks-date {{ \Carbon\Carbon::parse($task->due_date)->isPast() && !\Carbon\Carbon::parse($task->due_date)->isToday() ? 'is-overdue' : '' }}">
                                        <i class="bi bi-calendar3"></i>{{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="tasks-muted">—</span>
                                @endif
                            </td>
                            <td class="text-nowrap tasks-time-cell">{{ $task->getTotalElapsedTime() }}</td>
                            <td class="tasks-status-cell">
                                @if ($task->status === 'completed' || $task->story?->status === 'completed')
                                    {!! $task->status_badge !!}
                                @else
                                <div class="dropdown">
                                    <button class="task-status-btn tasks-status-toggle" type="button" id="dropdownMenuButton{{ $task->id }}"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        {!! $task->status_badge !!}
                                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end story-status-menu" aria-labelledby="dropdownMenuButton{{ $task->id }}">
                                        @foreach (config('site.tasks.status') as $key => $value)
                                            @if ($key != 'closed')
                                                <li>
                                                    <form action="{{ route('tasks.status.update', $task->id) }}" method="POST" class="mb-0">
                                                        @csrf
                                                        <input type="hidden" name="status" value="{{ $key }}">
                                                        <button type="submit" class="dropdown-item story-status-option status-option-{{ $key }} {{ $task->status === $key ? 'active' : '' }}">
                                                            {{ $value }}
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ optional(auth()->user())->can('tasks-bulk-complete') ? 10 : 9 }}" class="text-center py-5 text-muted">
                                No tasks match your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination matching Projects & Clients layout --}}
        {{ $tasks->links('pagination.pm', ['entity' => 'Tasks']) }}
    </div>

    {{-- Task Create Modal --}}
    {{-- Export Modal --}}
    @can('tasks-export')
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
                            <div class="mb-3">
                                <label for="from_date" class="form-label">From Date</label>
                                <input id="from_date" class="form-control" type="date" name="from_date" required>
                            </div>
                            <div class="mb-0">
                                <label for="to_date" class="form-label">To Date</label>
                                <input id="to_date" class="form-control" type="date" name="to_date" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Download</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const filterProjects = @json($filterProjects);
            const allFilterUsers = Array.from(document.querySelectorAll('#filter_user option'))
                .filter(option => option.value !== '')
                .map(option => ({id: String(option.value), name: option.textContent.trim()}));

            function replaceFilterOptions(select, items, emptyLabel, selectedValue) {
                const currentValue = String(selectedValue ?? '');
                select.innerHTML = '';
                select.add(new Option(emptyLabel, ''));
                items.forEach(item => select.add(new Option(item.name, String(item.id))));
                select.value = items.some(item => String(item.id) === currentValue) ? currentValue : '';
                $(select).trigger('change.select2');
            }

            function refreshTaskFilterOptions(changedFilter = null) {
                const clientSelect = document.getElementById('filter_client');
                const userSelect = document.getElementById('filter_user');
                const projectSelect = document.getElementById('filter_project');
                let clientId = String(clientSelect.value || '');
                let userId = String(userSelect.value || '');
                let projectId = String(projectSelect.value || '');
                let selectedProject = filterProjects.find(project => project.id === projectId);

                if (selectedProject && !clientSelect.disabled && (!clientId || changedFilter === 'project')) {
                    clientId = selectedProject.client_id;
                    clientSelect.value = clientId;
                    $(clientSelect).trigger('change.select2');
                }

                let projectsForClient = filterProjects.filter(project => !clientId || project.client_id === clientId);
                const clientUserIds = new Set(projectsForClient.flatMap(project => project.members));

                if (userId && clientId && !clientUserIds.has(userId)) {
                    userId = '';
                    userSelect.value = '';
                    $(userSelect).trigger('change.select2');
                }

                if (projectId) {
                    const projectMatchesFilters = projectsForClient.some(project =>
                        project.id === projectId && (!userId || project.members.includes(userId))
                    );
                    if (!projectMatchesFilters) {
                        projectId = '';
                        projectSelect.value = '';
                        selectedProject = null;
                    }
                }

                const projectOptions = filterProjects.filter(project =>
                    (!clientId || project.client_id === clientId) &&
                    (!userId || project.members.includes(userId))
                );
                const userProjectSet = projectId
                    ? filterProjects.filter(project => project.id === projectId)
                    : projectsForClient;
                const allowedUserIds = clientId || projectId
                    ? new Set(userProjectSet.flatMap(project => project.members))
                    : new Set(allFilterUsers.map(user => user.id));
                const userOptions = allFilterUsers.filter(user => allowedUserIds.has(user.id));

                if (userId && !allowedUserIds.has(userId)) {
                    userId = '';
                    userSelect.value = '';
                }

                replaceFilterOptions(projectSelect, projectOptions, 'All Projects', projectId);
                replaceFilterOptions(userSelect, userOptions, 'All Employees', userId);
            }

            refreshTaskFilterOptions();
            $('#filter_client').on('change', function() { refreshTaskFilterOptions('client'); });
            $('#filter_user').on('change', function() { refreshTaskFilterOptions('user'); });
            $('#filter_project').on('change', function() { refreshTaskFilterOptions('project'); });

            @can('tasks-bulk-complete')
                $('.check').on('change', function() {
                    updateSubmitButtonState();
                });

                window.checkAll = function() {
                    var isChecked = $('.maincheck').prop('checked');
                    $('.check').prop('checked', isChecked);
                    updateSubmitButtonState();
                };

                function updateSubmitButtonState() {
                    if ($('.check:checked').length > 0) {
                        $('#markCompleteBtn').prop('disabled', false);
                    } else {
                        $('#markCompleteBtn').prop('disabled', true);
                    }
                }

                window.submitCompletedTasks = function() {
                    var selectedTasks = [];

                    $('.check:checked').each(function() {
                        selectedTasks.push($(this).val());
                    });

                    if (selectedTasks.length === 0) {
                        toast('Please select at least one task.', 'warning');
                        return;
                    }

                    $.ajax({
                        url: "{{ route('tasks.bulk-complete') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            task_ids: selectedTasks,
                        },
                        success: function(response) {
                            toast('Tasks marked as complete successfully!', 'success');
                            location.reload();
                        },
                        error: function(xhr) {
                            console.error('Error marking tasks complete:', xhr.responseText);
                        }
                    });
                };
            @endcan
        });
    </script>
@endpush
