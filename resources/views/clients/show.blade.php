@extends('layouts.app')

@section('title', $client->name . ' - Client Details')

@section('content')
    @php
        $emailParts = $client->client_email ? explode('@', $client->client_email, 2) : [];
        $maskedEmail = count($emailParts) === 2
            ? mb_substr($emailParts[0], 0, 1) . str_repeat('•', max(3, min(7, mb_strlen($emailParts[0]) - 1))) . '@' . $emailParts[1]
            : '—';
        $serviceStatus = $currentService?->service_status;
        $serviceStatusLabel = $serviceStatus ? ucfirst(str_replace('_', ' ', $serviceStatus)) : 'No service';
    @endphp

    <main class="container-fluid mt-80 client-show-page">

        <header class="client-show-heading">
            <div class="client-show-heading-copy">
                <span class="client-show-eyebrow">CLIENT OVERVIEW</span>
                <h1>{{ $client->name }}</h1>
                <p>Services, assigned resources, and project activity in one place.</p>
            </div>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to clients
            </a>
        </header>

        <section class="client-show-summary" aria-label="Client details">
            <article class="client-summary-item client-summary-email">
                <span class="client-summary-label"><i class="bi bi-envelope"></i> Client email</span>
                <strong title="Email address masked for privacy">{{ $maskedEmail }}</strong>
            </article>
            <article class="client-summary-item">
                <span class="client-summary-label"><i class="bi bi-calendar3"></i> Service start date</span>
                <strong>{{ $currentService?->start_date ? \Carbon\Carbon::parse($currentService->start_date)->format('d M Y') : '—' }}</strong>
            </article>
            <article class="client-summary-item">
                <span class="client-summary-label"><i class="bi bi-activity"></i> Service status</span>
                <strong><span class="client-service-status client-service-status--{{ str_replace('_', '-', $serviceStatus ?: 'none') }}">{{ $serviceStatusLabel }}</span></strong>
            </article>
            <article class="client-summary-item">
                <span class="client-summary-label"><i class="bi bi-kanban"></i> Projects</span>
                <strong>{{ $projects->count() }}</strong>
            </article>
        </section>

        <section class="client-resource-grid" aria-label="Client resources">
            <article class="client-resource-card">
                <div class="client-section-heading">
                    <div>
                        <span class="client-section-icon"><i class="bi bi-people"></i></span>
                        <div>
                            <h2>Assigned users</h2>
                            <p>Resources assigned to this client’s projects</p>
                        </div>
                    </div>
                    <span class="client-resource-count">{{ $assignedUsers->count() }}</span>
                </div>
                <div class="client-user-chips">
                    @forelse ($assignedUsers as $user)
                        <span class="client-user-chip"><span>{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>{{ $user->name }}</span>
                    @empty
                        <span class="client-empty-inline">No users are assigned to these projects yet.</span>
                    @endforelse
                </div>
            </article>

            <article class="client-resource-card">
                <div class="client-section-heading">
                    <div>
                        <span class="client-section-icon client-section-icon--green"><i class="bi bi-person-check"></i></span>
                        <div>
                            <h2>Billable users</h2>
                            <p>Working resources listed on client services</p>
                        </div>
                    </div>
                    <span class="client-resource-count">{{ $billableUsers->count() }}</span>
                </div>
                <div class="client-user-chips">
                    @forelse ($billableUsers as $user)
                        <span class="client-user-chip"><span>{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>{{ $user->name }}</span>
                    @empty
                        <span class="client-empty-inline">No billable users are listed on the services.</span>
                    @endforelse
                </div>
                @if ($frontUsers->isNotEmpty())
                    <div class="client-front-users">
                        <span>Front-facing</span>
                        @foreach ($frontUsers as $user)
                            <span class="client-front-user"><i class="bi bi-person-badge"></i>{{ $user->name }}</span>
                        @endforeach
                    </div>
                @endif
            </article>

        </section>

        <section class="client-projects-section" aria-label="Client projects">
            <div class="client-projects-heading">
                <div>
                    <span class="client-show-eyebrow">WORKSPACE</span>
                    <h2>Projects <span>{{ $projects->count() }}</span></h2>
                </div>
            </div>

            @forelse ($projects as $project)
                @php
                    $projectTasks = $project->stories->flatMap(fn ($story) => $story->tasks)->concat($project->tasks);
                    $taskCount = $projectTasks->count();
                    $completedCount = $projectTasks->where('status', 'completed')->count();
                    $progress = $taskCount ? (int) round($completedCount * 100 / $taskCount) : 0;
                    $storyTaskIds = $project->stories->flatMap(fn ($story) => $story->tasks->pluck('id'))->all();
                    $otherTasks = $project->tasks->whereNotIn('id', $storyTaskIds);
                    $activeStories = $project->stories->reject(fn ($story) => $story->status === 'completed');
                    $completedStories = $project->stories->where('status', 'completed');
                @endphp
                <article class="client-project-board">
                    <header class="client-project-board-header">
                        <div class="client-project-title-wrap">
                            <span class="client-project-icon"><i class="bi bi-kanban-fill"></i></span>
                            <div>
                                <h3><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></h3>
                                <div class="client-project-meta">
                                    <span>{{ ucfirst(str_replace('_', ' ', $project->status ?? 'active')) }}</span>
                                    @if ($project->start_date)
                                        <span>{{ $project->start_date->format('d M Y') }}</span>
                                    @endif
                                    @if ($project->end_date)
                                        <span>Ends {{ $project->end_date->format('d M Y') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <a class="client-project-open" href="{{ route('projects.show', $project) }}" aria-label="Open {{ $project->name }}">
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                        <div class="client-project-progress">
                            <span>{{ $completedCount }}/{{ $taskCount }} tasks</span>
                            <strong>{{ $progress }}%</strong>
                            <div class="progress"><div class="progress-bar" style="width: {{ $progress }}%"></div></div>
                        </div>
                    </header>

                    <div class="client-project-board-body">
                        <div class="client-story-toolbar">
                            <span>Stories</span>
                            <div class="client-story-filters" role="tablist" aria-label="Filter stories for {{ $project->name }}">
                                <button type="button" class="active" role="tab" aria-selected="true" data-story-filter="active">
                                    Active <span>{{ $activeStories->count() }}</span>
                                </button>
                                <button type="button" role="tab" aria-selected="false" data-story-filter="completed">
                                    Completed <span>{{ $completedStories->count() }}</span>
                                </button>
                            </div>
                        </div>

                        <p class="client-story-empty {{ $activeStories->isNotEmpty() ? 'd-none' : '' }}" data-story-empty>
                            {{ $project->stories->isEmpty() ? 'This project has no stories yet.' : 'No active stories in this project.' }}
                        </p>

                        @foreach ($project->stories as $story)
                            @php $storyAccordionId = 'client-' . $client->id . '-project-' . $project->id . '-story-' . $story->id; @endphp
                            <div class="accordion client-story-accordion {{ $story->status === 'completed' ? 'd-none' : '' }}" data-client-story data-story-status="{{ $story->status === 'completed' ? 'completed' : 'active' }}" id="{{ $storyAccordionId }}-wrap">
                                <div class="accordion-item">
                                    <h4 class="accordion-header">
                                        <button class="accordion-button collapsed client-story-toggle" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#{{ $storyAccordionId }}"
                                            aria-expanded="false" aria-controls="{{ $storyAccordionId }}">
                                            <span class="client-story-mark"><i class="bi bi-bookmark"></i></span>
                                            <span class="client-story-title">{{ $story->title }}</span>
                                            <span class="client-story-count">{{ $story->tasks->count() }} tasks</span>
                                            <span class="client-story-status client-story-status--{{ str_replace('_', '-', $story->status) }}">{{ ucfirst(str_replace('_', ' ', $story->status)) }}</span>
                                        </button>
                                    </h4>
                                    <div id="{{ $storyAccordionId }}" class="accordion-collapse collapse" data-bs-parent="#{{ $storyAccordionId }}-wrap">
                                        <div class="accordion-body client-story-body">
                                            @if ($story->tasks->isNotEmpty())
                                                <div class="table-responsive">
                                                    <table class="table client-task-table align-middle">
                                                        <thead><tr><th>Task</th><th>Assigned to</th><th>Due date</th><th>Status</th><th>Time consumed</th></tr></thead>
                                                        <tbody>
                                                            @foreach ($story->tasks as $task)
                                                                @php
                                                                    $seconds = (int) ($task->tracker_histories_sum_elapsed_time ?? 0);
                                                                    $taskStatus = str_replace('_', '-', $task->status ?: 'unknown');
                                                                @endphp
                                                                <tr>
                                                                    <td><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></td>
                                                                    <td>{{ $task->user?->name ?? '—' }}</td>
                                                                    <td>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : '—' }}</td>
                                                                    <td><span class="client-task-status client-task-status--{{ $taskStatus }}">{{ ucfirst(str_replace('_', ' ', $task->status ?? 'unknown')) }}</span></td>
                                                                    <td>{{ intdiv($seconds, 3600) }}h {{ intdiv($seconds % 3600, 60) }}m</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="client-empty-inline mb-0">No tasks in this story.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if ($otherTasks->isNotEmpty())
                            @php $otherTasksId = 'client-' . $client->id . '-project-' . $project->id . '-other-tasks'; @endphp
                            <div class="accordion client-story-accordion" id="{{ $otherTasksId }}-wrap">
                                <div class="accordion-item">
                                    <h4 class="accordion-header">
                                        <button class="accordion-button collapsed client-story-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $otherTasksId }}">
                                            <span class="client-story-mark"><i class="bi bi-list-task"></i></span>
                                            <span class="client-story-title">Tasks without a story</span>
                                            <span class="client-story-count">{{ $otherTasks->count() }} tasks</span>
                                        </button>
                                    </h4>
                                    <div id="{{ $otherTasksId }}" class="accordion-collapse collapse" data-bs-parent="#{{ $otherTasksId }}-wrap">
                                        <div class="accordion-body client-story-body">
                                            <div class="table-responsive">
                                                <table class="table client-task-table align-middle">
                                                    <thead><tr><th>Task</th><th>Assigned to</th><th>Due date</th><th>Status</th><th>Time consumed</th></tr></thead>
                                                    <tbody>
                                                        @foreach ($otherTasks as $task)
                                                            @php
                                                                $seconds = (int) ($task->tracker_histories_sum_elapsed_time ?? 0);
                                                                $taskStatus = str_replace('_', '-', $task->status ?: 'unknown');
                                                            @endphp
                                                            <tr>
                                                                <td><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></td>
                                                                <td>{{ $task->user?->name ?? '—' }}</td>
                                                                <td>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : '—' }}</td>
                                                                <td><span class="client-task-status client-task-status--{{ $taskStatus }}">{{ ucfirst(str_replace('_', ' ', $task->status ?? 'unknown')) }}</span></td>
                                                                <td>{{ intdiv($seconds, 3600) }}h {{ intdiv($seconds % 3600, 60) }}m</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="client-empty-projects">
                    <span><i class="bi bi-kanban"></i></span>
                    <h3>No projects yet</h3>
                    <p>Projects linked to this client will appear here.</p>
                </div>
            @endforelse
        </section>
    </main>

@endsection

@push('styles')
    <style>
        .client-show-page { padding-bottom: 32px; }
        .client-show-heading { display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:18px; padding:6px 0; }
        .client-show-heading-copy h1 { margin:3px 0 4px; color:#132b4b; font-size:30px; font-weight:700; }
        .client-show-heading-copy p { margin:0; color:#71839d; font-size:13px; }
        .client-show-eyebrow { color:#2478f2; font-size:10px; font-weight:800; letter-spacing:.12em; }
        .client-show-summary { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:14px; }
        .client-summary-item,.client-resource-card,.client-project-board,.client-empty-projects { border:1px solid #dce6f3; border-radius:12px; background:#fff; box-shadow:0 3px 10px rgba(24,55,92,.045); }
        .client-summary-item { min-height:94px; padding:16px 18px; display:flex; flex-direction:column; justify-content:center; gap:9px; position:relative; overflow:hidden; background:radial-gradient(ellipse at 100% 0%, rgba(74,144,226,.10), transparent 48%), linear-gradient(135deg,#fff 0%,#f7faff 100%); }
        .client-summary-item:nth-child(2) { background:radial-gradient(ellipse at 100% 0%, rgba(14,165,233,.10), transparent 48%),linear-gradient(135deg,#fff 0%,#f3faff 100%); }
        .client-summary-item:nth-child(3) { background:radial-gradient(ellipse at 100% 0%, rgba(16,185,129,.10), transparent 48%),linear-gradient(135deg,#fff 0%,#f4fcf8 100%); }
        .client-summary-item:nth-child(4) { background:radial-gradient(ellipse at 100% 0%, rgba(139,92,246,.10), transparent 48%),linear-gradient(135deg,#fff 0%,#f8f6ff 100%); }
        .client-summary-label { display:flex; align-items:center; gap:8px; color:#71839d; font-size:12px; }
        .client-summary-label i { color:#2478f2; font-size:14px; }
        .client-summary-item strong { color:#173454; font-size:15px; font-weight:700; }
        .client-service-status,.client-story-status,.client-task-status { display:inline-flex; align-items:center; border-radius:6px; padding:4px 9px; font-size:11px; font-weight:700; background:#eaf3ff; color:#1769d2; }
        .client-service-status--completed,.client-story-status--completed,.client-task-status--completed { color:#07834c; background:#e3f7ed; }
        .client-service-status--canceled,.client-service-status--replacement,.client-task-status--on-hold { color:#bb3545; background:#ffeaed; }
        .client-service-status--new,.client-service-status--none { color:#65758c; background:#eef2f6; }
        .client-resource-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; margin-bottom:28px; }
        .client-resource-card { min-height:145px; padding:18px 20px; }
        .client-section-heading,.client-section-heading>div { display:flex; align-items:center; }
        .client-section-heading { justify-content:space-between; gap:12px; margin-bottom:16px; }
        .client-section-heading>div { gap:11px; }
        .client-section-icon,.client-project-icon { display:grid; place-items:center; flex:0 0 38px; width:38px; height:38px; border-radius:10px; color:#1769e8; background:#eaf3ff; font-size:17px; }
        .client-section-icon--green { color:#0b9460; background:#e3f8ef; }
        .client-section-heading h2 { margin:0 0 3px; color:#183656; font-size:15px; font-weight:700; }
        .client-section-heading p { margin:0; color:#8291a6; font-size:11px; }
        .client-resource-count,.client-story-count { border-radius:20px; padding:4px 9px; color:#326bb5; background:#edf5ff; font-size:11px; font-weight:700; white-space:nowrap; }
        .client-user-chips { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
        .client-user-chip { display:inline-flex; align-items:center; gap:7px; border:1px solid #e3ebf5; border-radius:20px; padding:5px 10px 5px 5px; color:#344b66; background:#fbfdff; font-size:12px; font-weight:600; }
        .client-user-chip>span { display:grid; place-items:center; width:25px; height:25px; border-radius:50%; color:#1769d2; background:#e7f0ff; font-size:10px; font-weight:800; }
        .client-empty-inline { color:#8795a8; font-size:12px; }
        .client-front-users { display:flex; flex-wrap:wrap; align-items:center; gap:7px; border-top:1px solid #edf1f6; margin-top:14px; padding-top:11px; color:#71839d; font-size:11px; }
        .client-front-user { display:inline-flex; align-items:center; gap:5px; color:#37516e; font-weight:600; }
        .client-front-user i { color:#6e54d8; }
        .client-projects-heading { display:flex; align-items:end; justify-content:space-between; margin-bottom:12px; }
        .client-projects-heading h2 { margin:3px 0 0; color:#173454; font-size:21px; font-weight:700; }
        .client-projects-heading h2 span { margin-left:5px; border-radius:15px; padding:3px 8px; color:#3671bc; background:#eaf3ff; font-size:11px; vertical-align:middle; }
        .client-project-board { overflow:hidden; margin-bottom:14px; }
        .client-project-board-header { display:grid; grid-template-columns:minmax(0,1fr) 34px minmax(180px,240px); align-items:center; gap:16px; padding:15px 18px; border-bottom:1px solid #e7edf5; }
        .client-project-title-wrap { display:flex; align-items:center; gap:11px; min-width:0; }
        .client-project-icon { color:#7454db; background:#f1edff; }
        .client-project-title-wrap h3 { margin:0 0 5px; font-size:16px; font-weight:700; }
        .client-project-title-wrap h3 a,.client-task-table a { color:#1769e8; text-decoration:none; }
        .client-project-title-wrap h3 a:hover,.client-task-table a:hover { text-decoration:underline; }
        .client-project-meta { display:flex; flex-wrap:wrap; gap:7px; color:#7c8ba0; font-size:11px; }
        .client-project-meta span+span:before { content:'•'; margin-right:7px; color:#b5c2d2; }
        .client-project-open { display:grid; place-items:center; width:32px; height:32px; border:1px solid #dbe5f1; border-radius:8px; color:#526b88; }
        .client-project-open:hover { color:#1769e8; background:#f3f8ff; }
        .client-project-progress { display:grid; grid-template-columns:1fr auto; align-items:center; gap:5px 10px; color:#7b8ba0; font-size:10px; }
        .client-project-progress strong { color:#274967; font-size:12px; }
        .client-project-progress .progress { grid-column:1 / -1; height:6px; border-radius:8px; background:#eaf0f7; }
        .client-project-progress .progress-bar { border-radius:8px; background:#2478f2; }
        .client-project-board-body { padding:12px 16px 15px; }
        .client-story-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px; color:#72839a; font-size:12px; font-weight:700; }
        .client-story-filters { display:flex; align-items:center; gap:4px; border:1px solid #e0e9f4; border-radius:9px; padding:3px; background:#f8fafd; }
        .client-story-filters button { display:inline-flex; align-items:center; gap:6px; border:0; border-radius:6px; padding:6px 10px; color:#63758c; background:transparent; font-size:11px; font-weight:600; }
        .client-story-filters button.active { color:#1769d2; background:#fff; box-shadow:0 1px 3px rgba(22,55,93,.12); }
        .client-story-filters button span { border-radius:10px; padding:1px 6px; color:#63758c; background:#edf2f8; font-size:10px; }
        .client-story-filters button.active span { color:#1769d2; background:#eaf3ff; }
        .client-story-empty { margin:0 0 10px; border:1px dashed #dce6f1; border-radius:8px; padding:12px; color:#8593a7; text-align:center; font-size:12px; }
        .client-story-accordion { margin-bottom:8px; }
        .client-story-accordion:last-child { margin-bottom:0; }
        .client-story-accordion .accordion-item { overflow:hidden; border:1px solid #e3eaf3; border-radius:9px; }
        .client-story-toggle,.client-story-toggle:not(.collapsed) { display:flex; gap:9px; padding:11px 13px; color:#28445f; background:#fbfdff; box-shadow:none; font-size:12px; }
        .client-story-toggle:after { width:12px; height:12px; background-size:12px; }
        .client-story-mark { color:#7454db; }
        .client-story-title { flex:1; font-weight:700; }
        .client-story-status { margin-right:8px; }
        .client-story-status--in-progress { color:#ae6200; background:#fff2dc; }
        .client-story-status--on-hold { color:#bd394b; background:#ffeaed; }
        .client-story-body { padding:0; }
        .client-task-table { margin:0; font-size:12px; }
        .client-task-table thead th { padding:9px 12px; border-bottom:1px solid #e6edf5; color:#6f8198; background:#f7f9fc; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
        .client-task-table tbody td { padding:10px 12px; border-color:#edf1f6; color:#405771; }
        .client-task-table tbody tr:last-child td { border-bottom:0; }
        .client-task-table tbody td:first-child { min-width:220px; color:#183b5d; font-weight:600; }
        .client-task-status--in-progress { color:#a75c00; background:#fff1dc; }
        .client-task-status--in-review { color:#6541bd; background:#f0eaff; }
        .client-task-status--to-do { color:#65758c; background:#eef2f6; }
        .client-task-status--on-hold { color:#ba3446; background:#ffeaed; }
        .client-empty-projects { display:grid; place-items:center; padding:40px 20px; text-align:center; }
        .client-empty-projects>span { display:grid; place-items:center; width:48px; height:48px; margin-bottom:10px; border-radius:14px; color:#2675e8; background:#eaf3ff; font-size:20px; }
        .client-empty-projects h3 { margin:0 0 5px; color:#183656; font-size:15px; }
        .client-empty-projects p { margin:0; color:#8593a7; font-size:12px; }
        @media (max-width: 900px) { .client-show-summary { grid-template-columns:repeat(2,minmax(0,1fr)); } .client-resource-grid { grid-template-columns:1fr; } }
        @media (max-width: 600px) { .client-show-heading { align-items:flex-start; flex-direction:column; } .client-show-heading-copy h1 { font-size:24px; } .client-show-summary { grid-template-columns:1fr 1fr; gap:8px; } .client-summary-item { min-height:82px; padding:12px; } .client-project-board-header { grid-template-columns:minmax(0,1fr) 32px; } .client-project-progress { grid-column:1 / -1; } .client-project-board-body { padding:9px; } .client-task-table { min-width:650px; } }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).on('click', '.client-story-filters [data-story-filter]', function() {
            const $button = $(this);
            const status = String($button.data('story-filter'));
            const $board = $button.closest('.client-project-board');
            let visibleStories = 0;

            $board.find('.client-story-filters [data-story-filter]')
                .removeClass('active')
                .attr('aria-selected', 'false');
            $button.addClass('active').attr('aria-selected', 'true');

            $board.find('[data-client-story]').each(function() {
                const matches = String($(this).data('story-status')) === status;
                $(this).toggleClass('d-none', !matches);
                if (matches) visibleStories++;
            });

            const $empty = $board.find('[data-story-empty]');
            $empty.toggleClass('d-none', visibleStories > 0);
            if (visibleStories === 0) {
                $empty.text(status === 'completed' ? 'No completed stories in this project.' : 'No active stories in this project.');
            }
        });
    </script>
@endpush
