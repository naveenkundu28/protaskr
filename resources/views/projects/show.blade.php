@extends('layouts.app')

@section('title', $project->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/project-module.css') }}?v={{ filemtime(public_path('assets/project-module.css')) }}">
@endpush

@section('content')

    @php
        $tab = request('tab', 'overview');
    @endphp

    <div class="container-fluid mt-80">


        {{-- Breadcrumb --}}
        <div class="pm-crumb">
            <a href="{{ route('projects.index') }}">Projects</a>
            <i class="bi bi-chevron-right"></i>
            <span title="{{ $project->name }}">{{ \Illuminate\Support\Str::words($project->name, 3, '…') }}</span>
        </div>

        {{-- Project Header --}}
        <div class="pm-project-head edit_project_head">
            <div class="left_action_btn">
            <div class="pm-project-icon">
                <i class="bi bi-kanban"></i>
            </div>

            <div>
                <h1>
                    {{ $project->name }}

                    <span class="pm-badge pm-status-{{ $project->status }} {{ $project->status }}">
                        {{ str_replace('_', ' ', ucfirst($project->status)) }}
                    </span>
                </h1>

                <p>
                    {{ $project->client?->name ?: 'Internal project' }}
                    ·
                    {{ optional($project->start_date)->format('d M Y') ?: 'No start date' }}
                    —
                    {{ optional($project->end_date)->format('d M Y') ?: 'No end date' }}
                </p>
            </div>
            </div>
            <div class="right_action_btn">
                

                @can('projects-update')
                    <a
                        class="btn btn-outline-primary ms-auto edit_project"
                        href="{{ route('projects.index', array_merge(request()->except(['edit', 'page']), ['edit' => $project->id])) }}"
                    >
                        <i class="bi bi-pencil"></i> Edit Project
                    </a>
                @endcan
            </div>

        </div>

        {{-- Project Tabs --}}
        <nav class="pm-tabs pm-detail-tabs">

            @foreach (
                [
                    'overview' => 'Overview',
                    'stories' => 'Stories (' . $project->stories()->count() . ')',
                    'files' => 'Files',
                ] as $key => $label
            )

                <a
                    class="{{ $tab === $key ? 'active' : '' }}"
                    href="{{ route('projects.show', [$project, 'tab' => $key]) }}"
                >
                    {{ $label }}
                </a>

            @endforeach

        </nav>

        {{-- Tab Content --}}
        <div class="pm-card">

            {{-- Overview --}}
            @if ($tab === 'overview')
                @php
                    $storyTotal = $overview['story_total'];
                    $taskTotal = $overview['task_total'];
                    $trackedHours = intdiv($overview['tracked_seconds'], 3600);
                    $trackedMinutes = intdiv($overview['tracked_seconds'] % 3600, 60);
                    $estimatedHours = \App\Support\DurationFormatter::fromHours($overview['estimated_hours']);
                    $taskStatusCounts = $overview['tasks'];
                    $teamMembers = $project->members->unique('id')->values();
                    $taskPercent = fn ($status) => $taskTotal ? round(($taskStatusCounts->get($status, 0) / $taskTotal) * 100) : 0;
                    
                    $taskPercentExact = fn ($status) => $taskTotal ? ($taskStatusCounts->get($status, 0) / $taskTotal) * 100 : 0;
                    $todoEnd = $taskPercentExact('to_do');
                    $inProgressEnd = $todoEnd + $taskPercentExact('in_progress');
                    $inReviewEnd = $inProgressEnd + $taskPercentExact('in_review');
                    $completedEnd = $inReviewEnd + $taskPercentExact('completed');
               
                @endphp

                <div class="row g-4 p-4">

                    <div class="col-lg-12">

                        <h4 class="d-none">Project overview</h4>

                        <p class="text-muted d-none">
                            {{ $project->description ?: 'No description has been added yet.' }}
                        </p>
                        <div class="cards-grid">
    
                            <!-- Card 1: Total Stories -->
                            <div class="card card-purple">
                            <!-- Wave Graphic -->
                            <svg class="card-wave" viewBox="0 0 160 120" preserveAspectRatio="none">
                                <path d="M 0,90 Q 50,85 80,45 T 160,25 L 160,120 L 0,120 Z" fill="url(#grad-purple)" opacity="0.18"/>
                                <path d="M 0,90 Q 50,85 80,45 T 160,25" fill="none" stroke="#c084fc" stroke-width="1.8" opacity="0.5"/>
                                <defs>
                                <linearGradient id="grad-purple" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#c084fc"/>
                                    <stop offset="100%" stop-color="#ffffff"/>
                                </linearGradient>
                                </defs>
                            </svg>

                            <div class="card-top">
                                <div class="icon-bubble icon-purple">
                                <i class="bi bi-layers"></i>
                                </div>
                                <div class="metric-info">
                                <h3>Total Stories</h3>
                                <div class="value">{{ $storyTotal }}</div>
                                <div class="card-bottom">
                                <div class="status-list">
                                <div class="status-item"><span class="dot dot-green"></span> {{ $overview['stories']->get('completed', 0) }} Completed</div>
                                <div class="status-item"><span class="dot dot-blue"></span> {{ $overview['stories']->get('in_progress', 0) }} In Progress</div>
                                <div class="status-item"><span class="dot dot-gray"></span> {{ $overview['stories']->get('to_do', 0) }} To Do</div>
                            </div>
                                </div>
                            </div>

                            
                            </div>
                            </div>

                            <!-- Card 2: Total Tasks -->
                            <div class="card card-blue">
                            <!-- Wave Graphic -->
                            <svg class="card-wave" viewBox="0 0 160 120" preserveAspectRatio="none">
                                <path d="M 0,90 Q 50,85 80,45 T 160,25 L 160,120 L 0,120 Z" fill="url(#grad-blue)" opacity="0.18"/>
                                <path d="M 0,90 Q 50,85 80,45 T 160,25" fill="none" stroke="#60a5fa" stroke-width="1.8" opacity="0.5"/>
                                <defs>
                                <linearGradient id="grad-blue" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#60a5fa"/>
                                    <stop offset="100%" stop-color="#ffffff"/>
                                </linearGradient>
                                </defs>
                            </svg>

                            <div class="card-top">
                                <div class="icon-bubble icon-blue">
                                <i class="bi bi-check-square"></i>
                                </div>
                                <div class="metric-info">
                                <h3>Total Tasks</h3>
                                <div class="value">{{ $taskTotal }}</div>
                                <div class="card-bottom">
                                <div class="status-list">
                                <div class="status-item"><span class="dot dot-green"></span> {{ $taskStatusCounts->get('completed', 0) }} Completed</div>
                                <div class="status-item"><span class="dot dot-blue"></span> {{ $taskStatusCounts->get('in_progress', 0) }} In Progress</div>
                                <div class="status-item"><span class="dot dot-gray"></span> {{ $taskStatusCounts->get('to_do', 0) }} To Do</div>
                                </div>
                            </div>
                                </div>
                            </div>

                            
                            </div>

                            <!-- Card 3: Total Hours / Estimated Hours for clients -->
                            <div class="card card-green">
                            <!-- Wave Graphic -->
                            <svg class="card-wave" viewBox="0 0 160 120" preserveAspectRatio="none">
                                <path d="M 0,90 Q 50,85 80,45 T 160,25 L 160,120 L 0,120 Z" fill="url(#grad-green)" opacity="0.18"/>
                                <path d="M 0,90 Q 50,85 80,45 T 160,25" fill="none" stroke="#4ade80" stroke-width="1.8" opacity="0.5"/>
                                <defs>
                                <linearGradient id="grad-green" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#4ade80"/>
                                    <stop offset="100%" stop-color="#ffffff"/>
                                </linearGradient>
                                </defs>
                            </svg>

                            <div class="card-top">
                                <div class="icon-bubble icon-green">
                                <i class="bi bi-clock"></i>
                                </div>
                                <div class="metric-info">
                                <h3>{{ $showEstimatedHours ? 'Estimated Hours' : 'Total Hours' }}</h3>
                                <div class="value">{{ $showEstimatedHours ? $estimatedHours : $trackedHours . 'h ' . $trackedMinutes . 'm' }}</div>
                                <div class="card-bottom">
                                <p class="subtitle-text">{{ $showEstimatedHours ? 'Estimated effort for all tasks' : 'Time consumed so far' }}</p>
                            </div>
                                </div>
                            </div>

                            
                            </div>

                            <!-- Card 4: Team Members -->
                            <div class="card card-orange">
                            <!-- Wave Graphic -->
                            <svg class="card-wave" viewBox="0 0 160 120" preserveAspectRatio="none">
                                <path d="M 0,90 Q 50,85 80,45 T 160,25 L 160,120 L 0,120 Z" fill="url(#grad-orange)" opacity="0.18"/>
                                <path d="M 0,90 Q 50,85 80,45 T 160,25" fill="none" stroke="#fb923c" stroke-width="1.8" opacity="0.5"/>
                                <defs>
                                <linearGradient id="grad-orange" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#fb923c"/>
                                    <stop offset="100%" stop-color="#ffffff"/>
                                </linearGradient>
                                </defs>
                            </svg>

                            <div class="card-top">
                                <div class="icon-bubble icon-orange">
                                <i class="bi bi-people-fill"></i>
                                </div>
                                <div class="metric-info">
                                <h3>Team Members</h3>
                                <div class="value">{{ $teamMembers->count() }}</div>
                                <div class="card-bottom">
                                    <div class="avatars">
                                    @forelse ($teamMembers->take(4) as $member)
                                        @php
                                            // Derive a color from the member ID so each avatar gets a varied,
                                            // consistent background instead of changing on every page refresh.
                                            $avatarColors = ['#0284c7', '#7c3aed', '#db2777', '#ea580c', '#16a34a', '#0891b2'];
                                            $avatarColor = $avatarColors[abs(crc32((string) $member->id)) % count($avatarColors)];
                                        @endphp
                                        <div class="avatar" style="background-color: {{ $avatarColor }};" title="{{ $member->name }}">{{ strtoupper(collect(explode(' ', $member->name))->map(fn ($part) => substr($part, 0, 1))->take(2)->join('')) }}</div>
                                    @empty
                                        <span class="subtitle-text">No members assigned</span>
                                    @endforelse
                                    @if($teamMembers->count() > 4)
                                        <div class="avatar-add">+{{ $teamMembers->count() - 4 }}</div>
                                    @endif
                                    </div>
                                </div>
                                </div>
                                
                            </div>

                            
                            </div>

                        </div>

                        <div class="col-md-12">
                            <div class="task_status_container">
                                <!-- Task Status Card -->
                                <div class="card">
                                <div class="card-header">
                                    <h2 class="card-title">Task Status</h2>
                                </div>
                                <div class="project-progress">
                                    <div class="progress" role="progressbar" aria-label="Project progress" aria-valuenow="{{ $project->progress }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar {{ $project->progress_color_class }}" style="width: {{ $project->progress }}%"></div>
                                    </div>
                                    <span class="project-progress-text">Progress <strong>{{ $project->progress }}%</strong></span>
                                </div>
                                <div class="task-content">
                                    <div class="donut-chart" style="background: conic-gradient(
    #9333ea 0% {{ $todoEnd }}%,
    #f59e0b {{ $todoEnd }}% {{ $inProgressEnd }}%,
    #6b7280 {{ $inProgressEnd }}% {{ $inReviewEnd }}%,
    #22c55e {{ $inReviewEnd }}% 100%
);">
                                    <div class="donut-inner">
                                        <span class="donut-count">{{ $taskTotal }}</span>
                                        <span class="donut-label">Total Tasks</span>
                                    </div>
                                    </div>

                                    <ul class="status-list">
                                    <li class="status-item">
                                        <span class="status-dot dot-purple"></span>
                                        <span class="status-name">To Do</span>
                                        <span class="status-count">{{ $taskStatusCounts->get('to_do', 0) }}</span>
                                        <span class="status-percent">{{ $taskPercent('to_do') }}%</span>
                                    </li>
                                    <li class="status-item">
                                        <span class="status-dot dot-orange"></span>
                                        <span class="status-name">In Progress</span>
                                        <span class="status-count">{{ $taskStatusCounts->get('in_progress', 0) }}</span>
                                        <span class="status-percent">{{ $taskPercent('in_progress') }}%</span>
                                    </li>
                                    <li class="status-item">
                                        <span class="status-dot dot-gray"></span>
                                        <span class="status-name">In Review</span>
                                        <span class="status-count">{{ $taskStatusCounts->get('in_review', 0) }}</span>
                                        <span class="status-percent">{{ $taskPercent('in_review') }}%</span>
                                    </li>
                                    <li class="status-item">
                                        <span class="status-dot dot-green"></span>
                                        <span class="status-name">Completed</span>
                                        <span class="status-count">{{ $taskStatusCounts->get('completed', 0) }}</span>
                                        <span class="status-percent">{{ $taskPercent('completed') }}%</span>
                                    </li>
                                    </ul>
                                </div>
                                </div>

                                <!-- Project Details Card -->
                                <div class="card">
                                <div class="card-header">
                                    <h2 class="card-title">Project Details</h2>
                                    @can('projects-update')
                                        <a
                                            class="btn-edit"
                                            href="{{ route('projects.index', ['edit' => $project->id]) }}"
                                        >Edit</a>
                                    @endcan
                                </div>

                                <div class="details-list">
                                    <div class="detail-row">
                                    <div class="left_detail">
                                        <i class="bi bi-building"></i>
                                        <span class="detail-label">Client</span>
                                    </div>
                                    <span class="detail-value">
                                        @if ($project->client)
                                            <a href="{{ route('clients.show', $project->client) }}">{{ $project->client->name }}</a>
                                        @else
                                            Internal project
                                        @endif
                                    </span>
                                    </div>
                                    <div class="detail-row">
                                        <div class="left_detail">
                                            <i class="bi bi-tag-fill"></i>
                                            <span class="detail-label">Project Type</span>
                                        </div>
                                    <span class="detail-value">{{ ucfirst($project->project_type) }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <div class="left_detail">
                                            <i class="bi bi-heart-fill"></i>
                                            <span class="detail-label">Status</span>
                                        </div>
                                    <span class="detail-value"><span class="pm-badge pm-status-{{ $project->status }} {{ $project->status }}">{{ str_replace('_', ' ', ucfirst($project->status)) }}</span></span>
                                    </div>
                                    <div class="detail-row">
                                        <div class="left_detail">
                                            <i class="bi bi-calendar-check"></i>
                                            <span class="detail-label">Start Date</span>
                                        </div>
                                    <span class="detail-value">{{ optional($project->start_date)->format('d M Y') ?: '—' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <div class="left_detail">
                                            <i class="bi bi-calendar-check"></i>
                                            <span class="detail-label">End Date</span>
                                        </div>
                                    <span class="detail-value">{{ optional($project->end_date)->format('d M Y') ?: '—' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <div class="left_detail">
                                            <i class="bi bi-person-fill"></i>
                                            <span class="detail-label">Created By</span>
                                        </div>
                                    <span class="detail-value">{{ $project->creator?->name ?: '—' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <div class="left_detail">
                                        <i class="bi bi-calendar-check"></i>
                                        <span class="detail-label">Created On</span>
                                    </div>
                                    <span class="detail-value">{{ optional($project->created_at)->format('d M Y') ?: '—' }}</span>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>


                    </div>

                </div>

            {{-- Stories --}}
            @elseif ($tab === 'stories')

                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">

                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <h4 class="mb-0">Stories</h4>
                        <nav class="story-list-filters" aria-label="Filter stories">
                            <a class="{{ $storyFilter === 'active' ? 'active' : '' }}" href="{{ route('projects.show', [$project, 'tab' => 'stories', 'story_filter' => 'active']) }}" @if ($storyFilter === 'active') aria-current="page" @endif>
                                Active <span>({{ $storyCounts['active'] }})</span>
                            </a>
                            <a class="{{ $storyFilter === 'completed' ? 'active' : '' }}" href="{{ route('projects.show', [$project, 'tab' => 'stories', 'story_filter' => 'completed']) }}" @if ($storyFilter === 'completed') aria-current="page" @endif>
                                Completed <span>({{ $storyCounts['completed'] }})</span>
                            </a>
                        </nav>
                    </div>

                    @can('projects-stories-create')
                        <!-- <a
                            class="btn btn-primary pm-button theme_color"
                            href="{{ route('stories.create', ['project' => $project]) }}"
                        >
                            <i class="bi bi-plus-lg"></i>
                            Create Story
                        </a> -->
                        <a class="btn btn-primary pm-button theme_color" href="#" onclick="openPopup()">
                            <i class="bi bi-plus-lg"></i>
                            Create Story
                        </a>
                    @endcan

                </div>

                <div class="table-responsive project_table">

                    <table class="table pm-table mb-0 align-middle story-list-table"">

                        <thead>
                            <tr>
                                <th class="status_table_number">#</th>
                                <th class="status_table_title">Story Title</th>
                                <th class="status_table_points"><span class="d-block" style="font-size: 9px; line-height: 1;">Story</span>Points</th>
                                <th class="status_table_task">Tasks</th>
                                <th class="status_table_estimated">Est. Hours</th>
                                @if (!Auth::user()->hasRole(\App\Support\Roles::CLIENT))
                                <th class="status_table_tracked">T. Hours</th>
                                @endif
                                <th class="status_table_status">Status</th>
                                <th class="status_table_priority">Priority</th>
                                <th class="status_table_due_date">Due date</th>
                                <th class="status_table_action">Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($stories as $story)

                                <tr>
                                    <td>{{ $stories->firstItem() + $loop->index }}</td>
                                    <td>
                                        @if ((int) $story->running_tasks_count > 0)
                                            @php $runningLabel = (int) $story->running_tasks_count === 1 ? 'A task is running' : (int) $story->running_tasks_count . ' tasks are running'; @endphp
                                            <span class="pm-running-dot" title="{{ $runningLabel }}" aria-label="{{ $runningLabel }}"></span>
                                        @endif
                                        @can('tasks-show')
                                        <a
                                            class="pm-link fw-semibold"
                                            href="{{ route('stories.show', $story) }}"
                                        >
                                            {{ $story->title }}
                                        </a>
                                        @else
                                            <span class="fw-semibold">{{ $story->title }}</span>
                                        @endcan
                                        @if ($story->creator)
                                            <small class="d-block text-muted" style="font-size: 11px;">by {{ $story->creator->name }}</small>
                                        @endif
                                    </td>

                                    <td>{{ $story->story_points ?? 0 }}</td>

                                    <td>
                                        {{ $story->tasks_count }}
                                    </td>

                                    <td>
                                        {{ \App\Support\DurationFormatter::fromHours($story->estimated_hours_total) }}
                                    </td>

                                    @if (!Auth::user()->hasRole(\App\Support\Roles::CLIENT))
                                        <td>
                                            @php
                                                $trackedHours = intdiv((int) $story->tracked_seconds_total, 3600);
                                                $trackedMinutes = intdiv((int) $story->tracked_seconds_total % 3600, 60);
                                            @endphp
                                            
                                        {{ $trackedHours }}h {{ $trackedMinutes }}m
                                    </td>
                                    @endif
                                    <td>
                                        @can('projects-stories-update')
                                            @php
                                                $storyStatusOptions = ['to_do' => 'To Do', 'in_progress' => 'In Progress', 'in_review' => 'In Review', 'blocked' => 'On Hold', 'completed' => 'Completed'];
                                                $storyStatusStyle = $story->status === 'blocked' ? 'on_hold' : $story->status;
                                            @endphp
                                            <div class="dropdown story-status-dropdown story-list-status-dropdown">
                                                <button type="button" class="task-status task-status-toggle task-status-{{ $storyStatusStyle }}" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Change status for {{ $story->title }}" title="{{ $story->status === 'completed' ? 'Reopen the story before editing it or its tasks' : 'Change story status' }}">
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
                                            <span class="pm-badge {{ $story->status }}">{{ str_replace('_', ' ', ucfirst($story->status)) }}</span>
                                        @endcan
                                    </td>

                                    <td class="text-capitalize">
                                        @can('projects-stories-update')
                                            @if ($story->status !== 'completed')
                                            <div class="dropdown story-priority-dropdown">
                                                <button type="button" class="story-priority-toggle priority-{{ $story->priority }}" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Change priority for {{ $story->title }}">
                                                    <span>{{ $story->priority }}</span>
                                                    <i class="bi bi-chevron-down"></i>
                                                </button>
                                                <ul class="dropdown-menu story-status-menu shadow-sm">
                                                    @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] as $priorityValue => $priorityLabel)
                                                        <li>
                                                            <form method="POST" action="{{ route('stories.priority.update', $story) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="priority" value="{{ $priorityValue }}">
                                                                <button type="submit" class="dropdown-item story-status-option {{ $story->priority === $priorityValue ? 'active' : '' }}">
                                                                    <span class="story-priority-dot priority-dot-{{ $priorityValue }}"></span>{{ $priorityLabel }}
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            @else
                                                <span class="story-priority-static priority-{{ $story->priority }}">{{ $story->priority }}</span>
                                            @endif
                                        @else
                                            <span class="story-priority-static priority-{{ $story->priority }}">{{ $story->priority }}</span>
                                        @endcan
                                    </td>

                                    <td>
                                        @can('projects-stories-update')
                                            @if ($story->status !== 'completed')
                                            <form method="POST" action="{{ route('stories.due-date.update', $story) }}" class="story-due-date-form">
                                                @csrf
                                                @method('PATCH')
                                                <input
                                                    type="date"
                                                    name="due_date"
                                                    value="{{ optional($story->due_date)->format('Y-m-d') }}"
                                                    aria-label="Change due date for {{ $story->title }}"
                                                    title="Click to change due date"
                                                    onchange="this.form.submit()"
                                                    onclick="if (this.showPicker) this.showPicker()"
                                                >
                                                <span>{{ optional($story->due_date)->format('d M Y') ?: 'Set due date' }}</span>
                                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                            </form>
                                            @else
                                                {{ optional($story->due_date)->format('d M Y') ?: '—' }}
                                            @endif
                                        @else
                                            {{ optional($story->due_date)->format('d M Y') ?: '—' }}
                                        @endcan
                                    </td>
                                    <td class="action_emp_status">
                                        @if ($story->status !== 'completed')
                                            <div class="project-actions">
                                                <button type="button" class="emp_status_action" aria-label="Story actions">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul>
                                                    @can('projects-stories-update')
                                                        <li><a href="{{ route('projects.show', ['project' => $project, 'tab' => 'stories', 'edit_story' => $story->id]) }}">Edit</a></li>
                                                    @endcan
                                                    @can('projects-stories-delete')
                                                        <li>
                                                            <form method="POST" class="mb-0" action="{{ route('stories.destroy', $story) }}" onsubmit="return confirm('Delete this story?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="project-delete-action" type="submit">Delete</button>
                                                            </form>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        @else
                                            <span class="text-muted" title="Reopen the story to make changes"><i class="bi bi-lock-fill" aria-hidden="true"></i></span>
                                        @endif
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="10"
                                        class="text-center py-5 text-muted"
                                    >
                                        {{ $storyFilter === 'completed' ? 'No completed stories yet.' : 'No active stories yet.' }}
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>
                    @if ($stories->hasPages())
                        <div class="pagination">
                            <p>Showing {{ $stories->firstItem() }} to {{ $stories->lastItem() }} of {{ $stories->total() }} Stories</p>
                            <ul>
                                <li class="prev_next_btn {{ $stories->onFirstPage() ? 'disabled' : '' }}">
                                    @if ($stories->onFirstPage())
                                        <span><i class="bi bi-chevron-left"></i></span>
                                    @else
                                        <a href="{{ $stories->previousPageUrl() }}" aria-label="Previous page"><i class="bi bi-chevron-left"></i></a>
                                    @endif
                                </li>

                                @foreach ($stories->onEachSide(1)->elements() as $element)
                                    @if (is_string($element))
                                        <li class="disabled"><span>{{ $element }}</span></li>
                                    @else
                                        @foreach ($element as $page => $url)
                                            <li class="{{ $page === $stories->currentPage() ? 'active' : '' }}">
                                                <a href="{{ $url }}">{{ $page }}</a>
                                            </li>
                                        @endforeach
                                    @endif
                                @endforeach

                                <li class="prev_next_btn {{ $stories->hasMorePages() ? '' : 'disabled' }}">
                                    @if ($stories->hasMorePages())
                                        <a href="{{ $stories->nextPageUrl() }}" aria-label="Next page"><i class="bi bi-chevron-right"></i></a>
                                    @else
                                        <span><i class="bi bi-chevron-right"></i></span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                    @elseif ($stories->total())
                        <div class="pagination">
                            <p>Showing {{ $stories->firstItem() }} to {{ $stories->lastItem() }} of {{ $stories->total() }} Stories</p>
                        </div>
                    @endif

                </div>

            {{-- Tasks --}}

                <div class="modal fade pm-modal" id="storyModal" tabindex="-1" aria-labelledby="storyModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <form method="POST" action="{{ $formStory->exists ? route('stories.update', $formStory) : route('stories.store') }}" class="modal-content">
                            @csrf
                            @if($formStory->exists) @method('PUT') @endif
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            <div class="modal-header">
                                <div>
                                    <span class="pm-eyebrow d-block">{{ $project->name }}</span>
                                    <h5 class="modal-title fw-bold" id="storyModalLabel">{{ $formStory->exists ? 'Edit Story' : 'Create New Story' }}</h5>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeStoryModal()"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-3">
                                    {{-- Row 1: Project & Story Title --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Project</label>
                                        <input class="form-control bg-light" value="{{ $project->name }}" readonly disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Story Title <span class="text-danger">*</span></label>
                                        <input class="form-control" name="title" value="{{ old('title', $formStory->title) }}" placeholder="Enter story title" required>
                                    </div>

                                    {{-- Row 2: Priority & Due Date --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                                        <select class="form-select" name="priority">
                                            @foreach(['medium' => 'Medium', 'high' => 'High', 'low' => 'Low'] as $value => $label)
                                                <option value="{{ $value }}" @selected(old('priority', $formStory->priority ?: 'medium') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Due Date</label>
                                        <input class="form-control" type="date" name="due_date" value="{{ old('due_date', optional($formStory->due_date)->format('Y-m-d')) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="story_points">Story Point</label>
                                        <input class="form-control" type="number" id="story_points" name="story_points" min="0" step="1" value="{{ old('story_points', $formStory->story_points) }}">
                                    </div>

                                    {{-- Row 3: Description --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea class="form-control summernote" id="story_description" rows="5" name="description">{{ old('description', $formStory->description) }}</textarea>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold" for="acceptance_criteria">Acceptance Criteria</label>
                                        <textarea class="form-control summernote" id="acceptance_criteria" rows="5" name="acceptance_criteria">{{ old('acceptance_criteria', $formStory->acceptance_criteria) }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer px-4 py-3">
                                <button type="button" class="btn btn-cancel px-4" data-bs-dismiss="modal" onclick="closeStoryModal()">Cancel</button>
                                <button type="submit" class="btn btn-primary pm-button theme-color px-4">{{ $formStory->exists ? 'Save Changes' : 'Create Story' }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                function openStoryModal() {
                    var modalEl = document.getElementById('storyModal');
                    if (modalEl) {
                        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    }
                }
                function openPopup() {
                    openStoryModal();
                }
                function closeStoryModal() {
                    var modalEl = document.getElementById('storyModal');
                    if (modalEl) {
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                    window.history.replaceState({}, document.title, "{{ route('projects.show', [$project, 'tab' => 'stories']) }}");
                }
                function closePopup() {
                    closeStoryModal();
                }

                document.addEventListener('DOMContentLoaded', function () {
                    var modalEl = document.getElementById('storyModal');
                    if (modalEl) {
                        modalEl.addEventListener('hidden.bs.modal', function () {
                            window.history.replaceState({}, document.title, "{{ route('projects.show', [$project, 'tab' => 'stories']) }}");
                        });
                        modalEl.addEventListener('shown.bs.modal', function () {
                            if (typeof window.initSummernoteEditors === 'function') {
                                window.initSummernoteEditors();
                            }
                        });
                    }

                    @if($formStory->exists || $errors->any())
                    openStoryModal();
                    @endif
                });
                </script>



            {{-- @elseif ($tab === 'tasks')

                <div class="p-4">

                    <h4>Project Tasks</h4>

                    <p class="text-muted">
                        Tasks are organised inside stories.
                        Open a story to create, assign and track its tasks.
                    </p>

                    <a
                        class="btn btn-primary"
                        href="{{ route('projects.show', [$project, 'tab' => 'stories']) }}"
                    >
                        View stories
                    </a>

                </div> --}}

            {{-- Team --}}
            {{-- @elseif ($tab === 'team')

                <div class="p-4">

                    <h4>Project team</h4>

                    <p>
                        <b>Project manager:</b>
                        {{ $project->manager->name ?? 'Unassigned' }}
                    </p>

                    <div class="d-flex flex-wrap gap-2">

                        @forelse ($project->members as $member)

                            <span class="pm-member">
                                <i class="bi bi-person-circle"></i>
                                {{ $member->name }}
                            </span>

                        @empty

                            <p class="text-muted">
                                No team members have been assigned.
                            </p>

                        @endforelse

                    </div>

                </div> --}}

            {{-- Other Tabs --}}
            @else
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                <div><h4 class="mb-0">Project Files</h4>@if($currentFolder)<a href="{{ route('projects.show', array_filter(['project' => $project, 'tab' => 'files', 'folder' => $currentFolder->parent_id])) }}">← Back to files</a>@endif</div>
                <div class="d-flex gap-2"><details class="position-relative"><summary class="btn btn-primary pm-button theme-color"><i class="bi bi-upload"></i> Upload</summary><form class="card p-3 position-absolute end-0 mt-2" method="POST" action="{{ route('projects.files.store',$project) }}" enctype="multipart/form-data" style="z-index:10;width:280px">@csrf<input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}"><input class="form-control mb-2" type="file" name="file" required><button class="btn btn-primary w-100">Upload file</button></form></details><button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createFolderModal"><i class="bi bi-folder-plus"></i> Create Folder</button></div>
            </div>
            @if($folders->isNotEmpty() || $projectFiles->isNotEmpty())
                <div class="table-responsive project_table"><table class="table pm-table mb-0 align-middle"><thead><tr><th>Name</th><th>Type</th><th>Size</th><th>Uploaded by</th><th>Date</th><th>Actions</th></tr></thead><tbody>
                @foreach($folders as $folder)<tr><td><i class="bi bi-folder-fill"></i> <a class="project-file-name" title="{{ $folder->name }}" href="{{ route('projects.show',['project'=>$project,'tab'=>'files','folder'=>$folder->id]) }}">{{ $folder->name }}</a></td><td>Folder</td><td>—</td><td>{{ $folder->creator->name }}</td><td>{{ $folder->created_at->format('d M Y') }}</td><td><div class="project-actions"><button type="button" class="emp_status_action" aria-label="Folder actions"><i class="bi bi-three-dots"></i></button><ul><li><a href="{{ route('projects.folders.download',[$project,$folder]) }}"><i class="bi bi-download"></i> Download</a></li><li><form method="POST" class="mb-0" action="{{ route('projects.folders.destroy',[$project,$folder]) }}" onsubmit="return confirm('Delete this folder and its contents?')">@csrf @method('DELETE')<button class="project-delete-action">Delete</button></form></li></ul></div></td></tr>@endforeach
                @foreach($projectFiles as $file)<tr><td><i class="bi bi-file-earmark"></i> <a class="project-file-name" title="{{ $file->name }}" href="{{ route('projects.files.download',[$project,$file]) }}">{{ $file->name }}</a></td><td>{{ $file->mime_type ?: 'File' }}</td><td>{{ number_format($file->size / 1024, 1) }} KB</td><td>{{ $file->uploader->name }}</td><td>{{ $file->created_at->format('d M Y') }}</td><td><div class="project-actions"><button type="button" class="emp_status_action" aria-label="File actions"><i class="bi bi-three-dots"></i></button><ul><li><a href="{{ route('projects.files.download',[$project,$file]) }}"><i class="bi bi-download"></i> Download</a></li><li><form method="POST" class="mb-0" action="{{ route('projects.files.destroy',[$project,$file]) }}" onsubmit="return confirm('Delete this file?')">@csrf @method('DELETE')<button class="project-delete-action">Delete</button></form></li></ul></div></td></tr>@endforeach
                </tbody></table></div>
            @else
                <div class="no_files text-center py-5"><i class="bi bi-folder2-open fs-1 d-block mb-3"></i><h4>No files yet</h4><p>Upload files or create a folder to organise this project.</p><div class="file_action_grp"><details class="position-relative d-inline-block"><summary class="btn btn-primary"><i class="bi bi-upload"></i> Upload files</summary><form class="card p-3 position-absolute start-50 translate-middle-x mt-2" method="POST" action="{{ route('projects.files.store',$project) }}" enctype="multipart/form-data" style="z-index:10;width:280px">@csrf<input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}"><input class="form-control mb-2" type="file" name="file" required><button class="btn btn-primary w-100">Upload file</button></form></details> <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createFolderModal"><i class="bi bi-plus-lg"></i> Create Folder</button></div></div>
            @endif
            <div class="modal fade" id="createFolderModal" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('projects.folders.store',$project) }}" class="modal-content">@csrf<input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}"><div class="modal-header"><h5 class="modal-title">Create Folder</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">Folder name</label><input class="form-control" name="name" required autofocus></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Create Folder</button></div></form></div></div>
            @if(false)
            <div class="cards-grid">
    
                            <!-- Card 1: Total Stories -->
                            <div class="card card-purple">
                            <!-- Wave Graphic -->
                            <svg class="card-wave" viewBox="0 0 160 120" preserveAspectRatio="none">
                                <path d="M 0,90 Q 50,85 80,45 T 160,25 L 160,120 L 0,120 Z" fill="url(#grad-purple)" opacity="0.18"></path>
                                <path d="M 0,90 Q 50,85 80,45 T 160,25" fill="none" stroke="#c084fc" stroke-width="1.8" opacity="0.5"></path>
                                <defs>
                                <linearGradient id="grad-purple" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#c084fc"></stop>
                                    <stop offset="100%" stop-color="#ffffff"></stop>
                                </linearGradient>
                                </defs>
                            </svg>

                            <div class="card-top">
                                <div class="icon-bubble icon-purple">
                                <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <div class="metric-info">
                                <h3>Total Files</h3>
                                <div class="value">1</div>
                                
                            </div>

                            
                            </div>
                            </div>

                            <!-- Card 2: Total Tasks -->
                            <div class="card card-blue">
                            <!-- Wave Graphic -->
                            <svg class="card-wave" viewBox="0 0 160 120" preserveAspectRatio="none">
                                <path d="M 0,90 Q 50,85 80,45 T 160,25 L 160,120 L 0,120 Z" fill="url(#grad-blue)" opacity="0.18"></path>
                                <path d="M 0,90 Q 50,85 80,45 T 160,25" fill="none" stroke="#60a5fa" stroke-width="1.8" opacity="0.5"></path>
                                <defs>
                                <linearGradient id="grad-blue" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#60a5fa"></stop>
                                    <stop offset="100%" stop-color="#ffffff"></stop>
                                </linearGradient>
                                </defs>
                            </svg>

                            <div class="card-top">
                                <div class="icon-bubble icon-blue">
                                <i class="bi bi-folder-fill"></i>
                                </div>
                                <div class="metric-info">
                                <h3>Total Folders</h3>
                                <div class="value">1</div>
                                
                                </div>
                            </div>

                            
                            </div>

                            <!-- Card 3: Total Hours -->
                            <div class="card card-green">
                            <!-- Wave Graphic -->
                            <svg class="card-wave" viewBox="0 0 160 120" preserveAspectRatio="none">
                                <path d="M 0,90 Q 50,85 80,45 T 160,25 L 160,120 L 0,120 Z" fill="url(#grad-green)" opacity="0.18"></path>
                                <path d="M 0,90 Q 50,85 80,45 T 160,25" fill="none" stroke="#4ade80" stroke-width="1.8" opacity="0.5"></path>
                                <defs>
                                <linearGradient id="grad-green" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#4ade80"></stop>
                                    <stop offset="100%" stop-color="#ffffff"></stop>
                                </linearGradient>
                                </defs>
                            </svg>

                            <div class="card-top">
                                <div class="icon-bubble icon-green">
                                <i class="bi bi-cloud-arrow-up"></i>
                                </div>
                                <div class="metric-info">
                                <h3>Total Storage</h3>
                                <div class="value">0h</div>
                                
                                </div>
                            </div>

                            
                            </div>

                            <!-- Card 4: Team Members -->
                            

                        </div>
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">

                    <h4 class="mb-0">
                        Project Files
                    </h4>

                    <div class="right_search_box files_search_box">
                        <div class="search_box">
                            <button><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"></path>
                                </svg>
                            </button>
                            <input type="text" name="" placeholder="Search Project...">
                        </div>
                        <a href="#" class="view_mode"><i class="bi bi-list-ul"></i></a>
                        <a href="#" class="view_mode"><i class="bi bi-grid"></i></a>
                        <a class="btn btn-primary pm-button theme-color" href="#">
                            <i class="bi bi-upload"></i> Upload
                        </a>
                    </div>
                    
                </div>
                <div class="table-responsive project_table">

                    <table class="table pm-table mb-0 align-middle">

                        <thead>
                            <tr>
                                <th class="file_table_check_box"><input type="checkbox" name="projects" value="files"></th>
                                <th class="file_table_name">Name <a href="#"><i class="bi bi-arrow-down-up"></i></a></th>
                                <th class="file_table_type">Type <a href="#"><i class="bi bi-arrow-down-up"></i></a></th>
                                <th class="file_table_size">Size <a href="#"><i class="bi bi-arrow-down-up"></i></a></th>
                                <th class="file_table_upload_by">Upload By <a href="#"><i class="bi bi-arrow-down-up"></i></a></th>
                                <th class="file_table_date">Date <a href="#"><i class="bi bi-arrow-down-up"></i></a></th>
                                <th class="file_table_action">Actions <a href="#"><i class="bi bi-arrow-down-up"></i></a></th>
                            </tr>
                        </thead>

                        <tbody>

                                <tr>
                                    <td><input type="checkbox" name="projects" value="files"></td>
                                    <td><i class="bi bi-folder-fill"></i> Moiz</td>
                                    <td>Folder</td>
                                    <td>20 MB</td>
                                    <td>Naveen</td>
                                    <td>08/Oct/2026</td>
                                    <td class="action_emp_status">
                                        <button class="emp_status_action">
                                            <i class="bi bi-three-dots"></i>
                                            <ul>
                                                <li><a href="#">Delete</a></li>
                                            </ul>
                                        </button>
                                    </td>

                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="projects" value="files"></td>
                                    <td><i class="bi bi-file-earmark-text"></i>Moiz</td>
                                    <td>Files</td>
                                    <td>20 MB</td>
                                    <td>Naveen</td>
                                    <td>08/Oct/2026</td>
                                    <td class="action_emp_status">
                                        <button class="emp_status_action">
                                            <i class="bi bi-three-dots"></i>
                                            <ul>
                                                <li><a href="#">Delete</a></li>
                                            </ul>
                                        </button>
                                    </td>

                                </tr>


                                <tr>
                                    <td
                                        colspan="7"
                                        class="text-center py-5 text-muted"
                                    >
                                        <div class="no_files">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 250" width="100%" height="100%">
                                                <defs>
                                                    <!-- Background Cloud Blur Filters -->
                                                    <filter id="soft-blur" x="-30%" y="-30%" width="160%" height="160%">
                                                    <feGaussianBlur stdDeviation="16" />
                                                    </filter>
                                                    <filter id="intense-blur" x="-30%" y="-30%" width="160%" height="160%">
                                                    <feGaussianBlur stdDeviation="24" />
                                                    </filter>

                                                    <!-- Folder Base Lighting Gradient -->
                                                    <linearGradient id="backFolderGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                                    <stop offset="0%" stop-color="#3b82f6" />
                                                    <stop offset="100%" stop-color="#1d64e8" />
                                                    </linearGradient>

                                                    <!-- Folder Front Cover Gradient -->
                                                    <linearGradient id="frontFolderGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                                    <stop offset="0%" stop-color="#72adff" />
                                                    <stop offset="100%" stop-color="#4d90fe" />
                                                    </linearGradient>

                                                    <!-- Document Fill Gradients -->
                                                    <linearGradient id="backDocGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                                    <stop offset="0%" stop-color="#eff6ff" />
                                                    <stop offset="100%" stop-color="#bfdbfe" />
                                                    </linearGradient>

                                                    <linearGradient id="frontDocGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                                    <stop offset="0%" stop-color="#ffffff" />
                                                    <stop offset="100%" stop-color="#dbeafe" />
                                                    </linearGradient>

                                                    <!-- Folder Inner Ambient Shadow -->
                                                    <linearGradient id="innerShadowGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                                    <stop offset="0%" stop-color="#1e40af" stop-opacity="0.35" />
                                                    <stop offset="100%" stop-color="#1e40af" stop-opacity="0" />
                                                    </linearGradient>

                                                    <!-- Soft Drop Shadow Filter for Folder -->
                                                    <filter id="folderShadow" x="-10%" y="-10%" width="125%" height="130%">
                                                    <feDropShadow dx="0" dy="12" stdDeviation="14" flood-color="#3b82f6" flood-opacity="0.22" />
                                                    </filter>
                                                </defs>

                                                <!-- BACKGROUND: Abstract Soft Blue Clouds / Blobs -->
                                                <g id="background-elements">
                                                    <!-- Main base blob -->
                                                    <path d="M 140 160 C 90 145 75 105 130 75 C 180 50 250 55 310 65 C 380 40 440 90 420 150 C 405 190 320 200 240 195 C 180 200 155 185 140 160 Z"
                                                        fill="#edf5ff"
                                                        filter="url(#intense-blur)" />

                                                    <!-- Accent floating circular highlights -->
                                                    <circle cx="115" cy="180" r="30" fill="#e0eeff" opacity="0.65" filter="url(#soft-blur)" />
                                                    <circle cx="370" cy="80" r="28" fill="#e2edfd" opacity="0.55" filter="url(#soft-blur)" />
                                                    <circle cx="410" cy="140" r="38" fill="#e8f2fe" opacity="0.75" filter="url(#soft-blur)" />
                                                    <ellipse cx="250" cy="205" rx="90" ry="16" fill="#dbeafe" opacity="0.45" filter="url(#soft-blur)" />
                                                </g>

                                                <!-- MAIN ILLUSTRATION GROUP -->
                                                <g id="folder-composition" transform="translate(0, 0)">
                                                    
                                                    <!-- FOLDER BACKPLATE -->
                                                    <g id="folder-back">
                                                    <path d="M 200 115 
                                                            C 200 109 204 105 210 105 
                                                            L 255 105 
                                                            C 260 105 264 107 267 111 
                                                            L 273 118 
                                                            C 275 120 278 122 282 122 
                                                            L 310 122 
                                                            C 316 122 320 126 320 132 
                                                            L 316 195 
                                                            C 316 200 311 204 305 204 
                                                            L 203 204 
                                                            C 197 204 193 200 193 195 
                                                            Z" 
                                                            fill="url(#backFolderGrad)" />
                                                    <!-- Dark cavity / depth inside the folder -->
                                                    <rect x="200" y="122" width="115" height="40" rx="3" fill="url(#innerShadowGrad)" />
                                                    </g>

                                                    <!-- PAPERS / DOCUMENTS INSERTED -->
                                                    <g id="documents">
                                                    <!-- Back Sheet (Straight) -->
                                                    <g id="back-document" transform="rotate(-3, 235, 120)">
                                                        <rect x="210" y="85" width="62" height="75" rx="5" 
                                                            fill="url(#backDocGrad)" stroke="#dbeafe" stroke-width="1" />
                                                        <!-- Content Preview Lines -->
                                                        <rect x="218" y="94" width="30" height="4" rx="2" fill="#bfdbfe" opacity="0.8" />
                                                        <rect x="218" y="103" width="44" height="3" rx="1.5" fill="#bfdbfe" opacity="0.6" />
                                                        <rect x="218" y="110" width="38" height="3" rx="1.5" fill="#bfdbfe" opacity="0.6" />
                                                    </g>

                                                    <!-- Front Sheet (Tilted Right) -->
                                                    <g id="front-document" transform="rotate(13, 268, 115)">
                                                        <rect x="236" y="75" width="68" height="84" rx="5.5" 
                                                            fill="url(#frontDocGrad)" stroke="#eff6ff" stroke-width="1.2" />
                                                        <!-- Inner Border / Margin Detail -->
                                                        <rect x="242" y="82" width="56" height="70" rx="3.5" 
                                                            fill="none" stroke="#93c5fd" stroke-width="1" stroke-opacity="0.3" />
                                                        <!-- Document Text Placeholders -->
                                                        <rect x="248" y="89" width="32" height="4" rx="2" fill="#93c5fd" opacity="0.9" />
                                                        <rect x="248" y="98" width="44" height="3.5" rx="1.75" fill="#93c5fd" opacity="0.75" />
                                                        <rect x="248" y="106" width="44" height="3.5" rx="1.75" fill="#93c5fd" opacity="0.75" />
                                                        <rect x="248" y="114" width="34" height="3.5" rx="1.75" fill="#93c5fd" opacity="0.75" />
                                                        <rect x="248" y="122" width="22" height="3.5" rx="1.75" fill="#93c5fd" opacity="0.75" />
                                                    </g>
                                                    </g>

                                                    <!-- FOLDER FRONT COVER (Isometric / Open View) -->
                                                    <g id="folder-front" filter="url(#folderShadow)">
                                                    <path d="M 216.5 131 
                                                            C 215 131 213.5 132 213 133.5 
                                                            L 199.5 197.5 
                                                            C 198.5 202.5 202.2 207 207.3 207 
                                                            L 314 207 
                                                            C 319 207 323 203 324 198 
                                                            L 332.5 141.5 
                                                            C 333.5 135.5 329 130.5 323 130.5 
                                                            L 278 130.5 
                                                            C 274.5 130.5 271.5 132.5 269.5 135 
                                                            L 264.5 140 
                                                            C 263.2 141.5 261 142.5 258.5 142.5 
                                                            L 221 142.5 
                                                            C 218 142.5 216 139.5 217.2 136.5 
                                                            L 218 134.5 
                                                            C 218.5 132.5 217.5 131 216.5 131 
                                                            Z" 
                                                            fill="url(#frontFolderGrad)" />
                                                    
                                                    <!-- Top Edge Lip / Subtle Highlight -->
                                                    <path d="M 221 142.5 L 258.5 142.5 C 261 142.5 263.2 141.5 264.5 140 L 269.5 135 C 271.5 132.5 274.5 130.5 278 130.5 L 323 130.5" 
                                                            fill="none" stroke="#99c4ff" stroke-width="1.2" stroke-linecap="round" opacity="0.8" />
                                                    </g>
                                                </g>
                                            </svg>
                                            <h4>No files yet</h4>
                                            <p>Upload files, documents, images or any project related resources. </p>
                                            <div class="file_action_grp">
                                                <a href="#" class="upload_files"><i class="bi bi-upload"></i> Upload Files</a>
                                                <a href="#" class="create-folder"><i class="bi bi-plus-lg"></i> Create Folder</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                        </tbody>

                    </table>
                    <div class="pagination">
                        <p>Showing 1 to 6 of 12 Projects</p>
                        <ul>
                            <li class="prev_next_btn"><a href="#"><i class="bi bi-chevron-left"></i></a></li>
                            <li class="active"><a href="#">1</a></li>
                            <li><a href="#">2</a></li>
                            <li class="prev_next_btn"><a href="#"><i class="bi bi-chevron-right"></i></a></li>
                        </ul>
                    </div>

                </div>
                <div class="p-5 text-center text-muted">

                    <i class="bi bi-tools fs-2 d-block mb-2"></i>

                    {{ ucfirst($tab) }}
                    is ready for project-specific data as it becomes available.

                </div>

            @endif

            @endif

        </div>

    </div>

    @if (session('completion_error'))
        <div class="modal fade story-completion-modal" id="storyCompletionErrorModal" tabindex="-1" aria-labelledby="storyCompletionErrorTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="completion-accent"></div>
                    <button type="button" class="btn-close completion-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="completion-content">
                        <div class="completion-icon"><i class="bi bi-list-check" aria-hidden="true"></i></div>
                        <div class="completion-eyebrow">Story progress</div>
                        <h2 class="completion-title" id="storyCompletionErrorTitle">A few tasks are still open</h2>
                        <p class="completion-message">{{ session('completion_error') }}</p>
                        <div class="completion-flow" aria-label="Complete all tasks, then complete the story">
                            <div class="completion-step"><span class="completion-step-number">1</span><span>Complete all tasks</span></div>
                            <i class="bi bi-arrow-right completion-flow-arrow" aria-hidden="true"></i>
                            <div class="completion-step"><span class="completion-step-number"><i class="bi bi-check2" aria-hidden="true"></i></span><span>Complete the story</span></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 completion-footer">
                        <button type="button" class="btn btn-primary text-white" data-bs-dismiss="modal">Got it</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var completionErrorModal = document.getElementById('storyCompletionErrorModal');
                if (completionErrorModal) bootstrap.Modal.getOrCreateInstance(completionErrorModal).show();
            });
        </script>
    @endif

@endsection
