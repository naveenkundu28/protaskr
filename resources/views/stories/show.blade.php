@extends('layouts.app')
@section('title',$story->title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/project-module.css') }}?v={{ filemtime(public_path('assets/project-module.css')) }}">
@endpush

@section('content')

<div class="container-fluid mt-80">

    {{-- Breadcrumb --}}
    <div class="pm-crumb">
        <a href="{{ route('projects.index') }}">
            Projects
        </a>

        <i class="bi bi-chevron-right"></i>

        <a href="{{ route('projects.show', [$story->project, 'tab' => 'stories']) }}" title="{{ $story->project->name }}">
            {{ \Illuminate\Support\Str::words($story->project->name, 3, '…') }}
        </a>

        <i class="bi bi-chevron-right"></i>

        <span title="{{ $story->title }}">{{ \Illuminate\Support\Str::words($story->title, 3, '…') }}</span>
    </div>

    {{-- Story summary --}}
    <section class="story-summary-card">
        <div class="story-summary-top">
            <div class="pm-project-icon story story-summary-project-icon">
                <i class="bi bi-diagram-3"></i>
            </div>

            <div class="story-summary-main">
                <div class="story-summary-title-row">
                    <h1 class="story-summary-title">
                    {{ $story->title }}
                    </h1>
                    @can('projects-stories-update')
                        @php
                            $storyStatusOptions = [
                                'to_do' => 'To Do',
                                'in_progress' => 'In Progress',
                                'in_review' => 'In Review',
                                'blocked' => 'On Hold',
                                'completed' => 'Completed',
                            ];
                            $storyStatusStyle = $story->status === 'blocked' ? 'on_hold' : $story->status;
                        @endphp
                        <div class="dropdown story-status-dropdown">
                            <button type="button" class="task-status task-status-toggle task-status-{{ $storyStatusStyle }}" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Change story status">
                                <span>{{ $storyStatusOptions[$story->status] ?? ucfirst(str_replace('_', ' ', $story->status)) }}</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu story-status-menu shadow-sm">
                                @foreach ($storyStatusOptions as $statusValue => $statusLabel)
                                    <li>
                                        <form method="POST" action="{{ route('stories.status.update', $story) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $statusValue }}">
                                            <button type="submit" class="dropdown-item story-status-option {{ $story->status === $statusValue ? 'active' : '' }}">
                                                <span class="task-status-dot task-status-dot-{{ $statusValue === 'blocked' ? 'on_hold' : $statusValue }}"></span>{{ $statusLabel }}
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <span class="pm-badge {{ $story->status }}">
                            {{ $story->status === 'blocked' ? 'On Hold' : str_replace('_', ' ', ucfirst($story->status)) }}
                        </span>
                    @endcan
                </div>
                <div class="story-description text-muted">
                    {!! $story->description ?: 'No story description has been added.' !!}
                </div>
            </div>

            <div class="story-summary-actions">
                <form class="story-task-search" method="GET">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <label class="visually-hidden" for="story-task-search">Search tasks</label>
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input id="story-task-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search tasks">
                </form>
                @can('tasks-create')
                    <button class="btn btn-primary pm-button theme-color story-create-task" onclick="openPopup()">
                        <i class="bi bi-plus-lg"></i> Create Task
                    </button>
                @endcan
            </div>
        </div>

        @if (filled($story->acceptance_criteria))
            <section class="story-summary-section story-acceptance-section">
                <div class="story-summary-section-icon acceptance-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="story-summary-section-body">
                    <h2>Acceptance Criteria</h2>
                    <div class="story-acceptance-content">
                        {!! $story->acceptance_criteria !!}
                    </div>
                </div>
            </section>
        @endif

        @if ($story->dueDateHistories->isNotEmpty())
            @php
                $dueDateTimeline = collect();
                $firstDueDateChange = $story->dueDateHistories->first();
                $formatDueDate = fn ($date) => strtolower($date->format('d-M-Y'));

                if ($firstDueDateChange->old_due_date) {
                    $dueDateTimeline->push($formatDueDate($firstDueDateChange->old_due_date));
                }

                foreach ($story->dueDateHistories as $dueDateChange) {
                    $dueDateTimeline->push(
                        $dueDateChange->new_due_date
                            ? $formatDueDate($dueDateChange->new_due_date)
                            : 'No due date'
                    );
                }
            @endphp
            <section class="story-summary-section story-due-date-section">
                <div class="story-summary-section-icon due-date-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="story-summary-section-body">
                    <h2>Due Date History</h2>
                    <div class="story-date-timeline">
                        @foreach ($dueDateTimeline as $dateIndex => $dateLabel)
                            @if ($dateIndex > 0)
                                <i class="bi bi-arrow-right story-date-arrow" aria-hidden="true"></i>
                            @endif
                            <span class="story-date-pill {{ $loop->last ? 'current' : '' }}">{{ $dateLabel }}</span>
                        @endforeach
                        <span class="story-date-updated"><i></i> Updated</span>
                    </div>
                </div>
            </section>
        @endif
    </section>

    <div class="task_tabs">
        <div class="pm-tabs">@foreach(['overview' => 'Overview', 'all'=>'All','to_do'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','completed'=>'Completed','on_hold'=>'On Hold'] as $key=>$label)<a class="task-tab-{{ $key }} {{ $tab === $key ? 'active' : '' }}" href="{{ route('stories.show', array_filter(['story' => $story, 'tab' => $key, 'search' => request('search')])) }}">{{ $label }}</a>@endforeach</div>
    </div>

    {{-- Create Task --}}
    {{-- <div class="collapse mb-4" id="createTask">

        <div class="pm-card p-4">

            <button class="btn btn-primary pm-button ms-auto theme-color" onclick="openPopup()"><i class="bi bi-plus-lg"></i> Create Task</button>

            <form
                class="row g-3"
                method="POST"
                action="{{ route('stories.tasks.store', $story) }}"
            >
                @csrf

                <div class="col-md-6">
                    <input
                        class="form-control"
                        name="title"
                        placeholder="Task title"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <select
                        class="form-select"
                        name="user_id"
                        required
                    >
                        <option value="">
                            Assign to
                        </option>

                        @foreach ($story->project->members as $member)
                            <option value="{{ $member->id }}">
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select
                        class="form-select"
                        name="priority"
                    >
                        <option value="medium">
                            Medium priority
                        </option>

                        <option value="high">
                            High priority
                        </option>

                        <option value="low">
                            Low priority
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <input
                        class="form-control"
                        name="description"
                        placeholder="Description (optional)"
                    >
                </div>

                <div class="col-md-3">
                    <input
                        class="form-control"
                        type="date"
                        name="due_date"
                    >
                </div>

                <div class="col-md-2">
                    <input
                        class="form-control"
                        type="number"
                        min="0"
                        step=".5"
                        name="estimated_hours"
                        placeholder="Est. hours"
                    >
                </div>

                
                <div class="col-md-1">
                    <button class="btn btn-primary w-100">
                        Add
                    </button>
                </div>

            </form>

        </div>

    </div> --}}

    {{-- Status Tabs --}}
    {{-- <div class="pm-tabs">

        @foreach (
            [
                'all' => 'All',
                'to_do' => 'To Do',
                'in_progress' => 'In Progress',
                'in_review' => 'In Review',
                'completed' => 'Completed',
                'on_hold' => 'On Hold',
            ] as $key => $label
        )
            <a
                class="{{ $status === $key ? 'active' : '' }}"
                href="{{ route(
                    'stories.show',
                    array_filter([
                        'story' => $story,
                        'status' => $key,
                        'search' => request('search'),
                    ])
                ) }}"
            >
                {{ $label }}
            </a>
        @endforeach

    </div> --}}

    @if ($tab === 'overview')
        @php
            $trackedHours = intdiv($overview['tracked_seconds'], 3600);
            $trackedMinutes = intdiv($overview['tracked_seconds'] % 3600, 60);
            $teamMembers = $overview['team_members'];
            $estimatedHours = rtrim(rtrim(number_format($overview['estimated_hours'], 2, '.', ''), '0'), '.');
            $taskStatuses = $overview['task_statuses'];
            $taskTotal = $overview['task_total'];
            $taskPercent = fn ($taskStatus) => $taskTotal ? round(($taskStatuses->get($taskStatus, 0) / $taskTotal) * 100) : 0;
            
            $taskPercentExact = fn ($status) => $taskTotal ? ($taskStatuses->get($status, 0) / $taskTotal) * 100 : 0;
            $todoEnd = $taskPercentExact('to_do');
            $inProgressEnd = $todoEnd + $taskPercentExact('in_progress');
            $inReviewEnd = $inProgressEnd + $taskPercentExact('in_review');
            $completedEnd = $inReviewEnd + $taskPercentExact('completed');
            
            
            $completedPercent = $taskPercent('completed');
            $project = $story->project;
        @endphp

        <div class="pm-card p-4">
            
            <div class="task_status_container mt-4">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Task Status</h2>
                    </div>
                    <div class="project-progress">
                        <div class="progress" role="progressbar" aria-label="Story task progress" aria-valuenow="{{ $completedPercent }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar {{ $completedPercent <= 20 ? 'progress-danger' : ($completedPercent <= 40 ? 'progress-purple' : ($completedPercent <= 60 ? 'progress-success' : 'progress-primary')) }}" style="width: {{ $completedPercent }}%"></div>
                        </div>
                        <span class="project-progress-text">Progress <strong>{{ $completedPercent }}%</strong></span>
                    </div>
                    <div class="task-content">
                       <div class="donut-chart" style="background: conic-gradient(
    #9333ea 0% {{ $todoEnd }}%,
    #f59e0b {{ $todoEnd }}% {{ $inProgressEnd }}%,
    #6b7280 {{ $inProgressEnd }}% {{ $inReviewEnd }}%,
    #22c55e {{ $inReviewEnd }}% {{ $completedEnd }}%
);">
                            <div class="donut-inner">
                                <span class="donut-count">{{ $taskTotal }}</span>
                                <span class="donut-label">Total Tasks</span>
                            </div>
                        </div>
                        <ul class="status-list">
                            @foreach ([
                                ['to_do', 'dot-purple', 'To Do'],
                                ['in_progress', 'dot-orange', 'In Progress'],
                                ['in_review', 'dot-gray', 'In Review'],
                                ['completed', 'dot-green', 'Completed'],
                            ] as [$taskStatus, $dotClass, $label])
                                <li class="status-item">
                                    <span class="status-dot {{ $dotClass }}"></span>
                                    <span class="status-name">{{ $label }}</span>
                                    <span class="status-count">{{ $taskStatuses->get($taskStatus, 0) }}</span>
                                    <span class="status-percent">{{ $taskPercent($taskStatus) }}%</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="cards-grid">
                    @foreach ([
                        ['purple', '#c084fc', 'layers', 'Total Tasks', $overview['task_total'], 'Tasks in this story'],
                        ['orange', '#fb923c', 'people-fill', 'Team Members', $teamMembers->count(), null],
                        ['blue', '#60a5fa', 'calendar-check', 'Estimate Hours', $estimatedHours . 'h', 'Planned effort'],
                        ['green', '#4ade80', 'clock', 'Total Hours', $trackedHours . 'h ' . $trackedMinutes . 'm', 'Time tracked so far'],
                    ] as [$color, $waveColor, $icon, $label, $value, $subtitle])
                        @if (Auth::user()->hasRole(\App\Support\Roles::CLIENT) && $label === 'Total Hours')
                            @continue
                        @endif
                        <div class="card card-{{ $color }}">
                            <svg class="card-wave" viewBox="0 0 160 120" preserveAspectRatio="none" aria-hidden="true" style="color: {{ $waveColor }};">
                                <path d="M 0,90 Q 50,85 80,45 T 160,25 L 160,120 L 0,120 Z" fill="currentColor" opacity=".12"/>
                                <path d="M 0,90 Q 50,85 80,45 T 160,25" fill="none" stroke="currentColor" stroke-width="1.8" opacity=".5"/>
                            </svg>
                            <div class="card-top">
                                <div class="icon-bubble icon-{{ $color }}"><i class="bi bi-{{ $icon }}"></i></div>
                                <div class="metric-info">
                                    <h3>{{ $label }}</h3>
                                    <div class="value">{{ $value }}</div>
                                    <div class="card-bottom">
                                        @if ($label === 'Total Tasks')
                                            <div class="card-status-list">
                                                <div class="card-status-item"><span class="dot dot-green"></span> {{ $taskStatuses->get('completed', 0) }} Completed</div>
                                                <div class="card-status-item"><span class="dot dot-blue"></span> {{ $taskStatuses->get('in_progress', 0) }} In Progress</div>
                                                <div class="card-status-item"><span class="dot dot-gray"></span> {{ $taskStatuses->get('to_do', 0) }} To Do</div>
                                            </div>
                                        @elseif ($label === 'Team Members')
                                            <div class="avatars">
                                                @forelse ($teamMembers->take(4) as $member)
                                                    @php
                                                        $avatarColors = ['#0284c7', '#7c3aed', '#db2777', '#ea580c', '#16a34a', '#0891b2'];
                                                        $avatarColor = $avatarColors[abs(crc32((string) $member->id)) % count($avatarColors)];
                                                        $initials = strtoupper(collect(explode(' ', $member->name))->map(fn ($part) => substr($part, 0, 1))->take(2)->join(''));
                                                    @endphp
                                                    <div class="avatar" style="background-color: {{ $avatarColor }};" title="{{ $member->name }}">{{ $initials }}</div>
                                                @empty
                                                    <span class="subtitle-text">No members assigned</span>
                                                @endforelse
                                                @if ($teamMembers->count() > 4)
                                                    <div class="avatar-add">+{{ $teamMembers->count() - 4 }}</div>
                                                @endif
                                            </div>
                                        @else
                                            <p class="subtitle-text">{{ $subtitle }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
    {{-- Task List --}}
    <div class="pm-card">
        {{-- Tasks Table --}}
        <div class="table-responsive">

            <table class="table pm-table align-middle">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Task title</th>
                        <th>Assigned to</th>
                        <th>Priority</th>
                        <th>Estimate Hours</th>
                        @if(!Auth::user()->hasRole(\App\Support\Roles::CLIENT))
                        <th>Time Tracked</th>
                        @endif
                        <th>Status</th>
                        <th>Due date</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($tasks as $task)

                        <tr>

                            <td>
                                {{ $tasks->firstItem() + $loop->index }}
                            </td>

                            <td>
                                @if (in_array($task->id, $runningTaskIds))
                                    <span class="pm-running-dot" title="Timer running" aria-label="Timer running"></span>
                                @endif
                                <a
                                    class="pm-link fw-semibold"
                                    href="{{ route('tasks.show', $task) }}"
                                >
                                    {{ $task->title }}
                                </a>
                            </td>

                            <td>
                                {{ $task->user->name ?? 'Unassigned' }}
                            </td>

                            <td>
                                <span class="pm-badge priority-{{ $task->priority }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>

                            <td>
                                {{ rtrim(rtrim(number_format($task->estimated_hours ?? 0, 2, '.', ''), '0'), '.') }} Hrs
                            </td>

                            @if(!Auth::user()->hasRole(\App\Support\Roles::CLIENT))
                            <td>
                                {{ $task->getTotalElapsedTime() }}
                            </td>
                            @endif

                            <td>
                                <span class="pm-badge task-status task-status-{{ $task->status }}">
                                    {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                </span>
                            </td>

                            <td>
                                {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : '—' }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="8"
                                class="text-center py-5 text-muted"
                            >
                                No tasks match this filter.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Pagination --}}
        <div class="p-3">
            {{ $tasks->links() }}
        </div>

    </div>
    @endif

</div>

@can('tasks-create')
<div class="modal fade pm-modal" id="storyTaskModal" tabindex="-1" aria-labelledby="storyTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="pm-eyebrow d-block">{{ $story->title }}</span>
                    <h5 class="modal-title fw-bold" id="storyTaskModalLabel">Create Task</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('stories.tasks.store', $story) }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Task Title <span class="text-danger">*</span></label>
                            <input class="form-control" name="title" placeholder="Enter task title" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assign To <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="user_id" required>
                                <option value="">Select team member</option>
                                @foreach($story->project->members as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="medium">Medium priority</option>
                                <option value="high">High priority</option>
                                <option value="low">Low priority</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Due Date</label>
                            <input class="form-control" type="date" name="due_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estimated Hours</label>
                            <input class="form-control" type="number" min="0" max="4" step=".5" name="estimated_hours" placeholder="e.g. 4" title="Maximum estimate is 4 hours">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control summernote" name="description" rows="4" placeholder="Task description (optional)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary pm-button theme-color px-4">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<script>
function openPopup() {
    var modalEl = document.getElementById("storyTaskModal");
    if (modalEl) {
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}
function closePopup() {
    var modalEl = document.getElementById("storyTaskModal");
    if (modalEl) {
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
}
document.addEventListener("DOMContentLoaded", function () {
    var modalEl = document.getElementById("storyTaskModal");
    if (modalEl) {
        modalEl.addEventListener("shown.bs.modal", function () {
            // the dropdown must live inside the modal, otherwise it opens behind it
            $(modalEl).find('.select2').each(function () {
                if ($(this).data('select2')) $(this).select2('destroy');
                $(this).select2({
                    placeholder: "Select team member",
                    dropdownParent: $(modalEl),
                    width: '100%'
                });
            });
            if (typeof window.initSummernoteEditors === 'function') {
                window.initSummernoteEditors();
            }
        });
    }
});
</script>
@endsection
