@extends('layouts.app')
@section('title', 'Daily Task Reports')

@section('content')
    <div class="container-fluid mt-80 daily-task-report-page">
        <header class="daily-task-report-header">
            <div>
                <span class="daily-task-report-eyebrow">TEAM ACTIVITY</span>
                <h1>Daily Task Reports</h1>
                <p>Review each team member’s tasks, projects, and tracked time.</p>
            </div>
            <div class="daily-task-report-controls">
                <div class="daily-task-report-date-filter">
                    <label for="tasksDate">Report date</label>
                    <div class="input-group">
                        <input type="date" class="form-control" id="tasksDate" name="tasksDate"
                            max="{{ now()->format('Y-m-d') }}" value="{{ now()->subDay()->format('Y-m-d') }}">
                        <button type="button" id="tasksDateButton" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                    </div>
                </div>
                <div class="daily-task-report-user-filter">
                    <label for="dailyTaskUserFilter">Team member</label>
                    <select id="dailyTaskUserFilter" class="form-select daily-task-user-select"
                        aria-label="Filter by team member">
                        <option value="">All team members</option>
                        @foreach ($teamMembers as $teamMember)
                            <option value="{{ $teamMember->id }}">{{ $teamMember->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </header>

        <section class="daily-task-report-panel">
            <div class="daily-task-report-panel-body">
                <ul class="nav nav-tabs daily-task-report-tabs" id="tagsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-all" data-bs-toggle="tab"
                            data-bs-target="#pane-all" type="button" role="tab" aria-controls="pane-all"
                            aria-selected="true" data-tag-id="" data-accordion="dailyTasksAccordion-all">
                            All
                        </button>
                    </li>

                    @foreach ($tags as $index => $tag)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-{{ $index }}" data-bs-toggle="tab"
                                data-bs-target="#pane-{{ $index }}" type="button" role="tab"
                                aria-controls="pane-{{ $index }}" aria-selected="false"
                                data-tag-id="{{ $tag->id }}" data-accordion="dailyTasksAccordion-{{ $index }}">
                                {{ $tag->name }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content daily-task-report-tab-content" id="tagsTabsContent">
                    <div class="tab-pane fade show active" id="pane-all" role="tabpanel"
                        aria-labelledby="tab-all" tabindex="0">
                        <div class="accordion dailyTasksAccordion daily-task-users" id="dailyTasksAccordion-all"></div>
                    </div>

                    @foreach ($tags as $index => $tag)
                        <div class="tab-pane fade" id="pane-{{ $index }}" role="tabpanel"
                            aria-labelledby="tab-{{ $index }}" tabindex="0">
                            <div class="accordion dailyTasksAccordion daily-task-users" id="dailyTasksAccordion-{{ $index }}"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            const taskShowRoute = "{{ route('tasks.show', ':id') }}";
            const projectShowRoute = "{{ route('projects.show', ':id') }}";
            const storyShowRoute = "{{ route('stories.show', ':id') }}";

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, character => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                })[character]);
            }

            function plainText(value) {
                const element = document.createElement('div');
                element.innerHTML = String(value ?? '');
                return (element.textContent || '').replace(/\s+/g, ' ').trim();
            }

            function loadTasks(accordionId = 'dailyTasksAccordion-all', tagId = '') {
                let date = $('#tasksDate').val();

                $.ajax({
                    url: "{{ route('reports.daily-task.users') }}",
                    type: "GET",
                    data: {
                        tasksDate: date,
                        tag_id: tagId
                    },
                    success: function(res) {
                        if (res.status) {
                            updateUserOptions(res.data || []);
                            initializeUserSelect();
                            renderTasks(res.data, accordionId);
                        }
                    }
                });
            }

            // On load
            loadTasks('dailyTasksAccordion-all');

            // On filter change
            $('#tasksDateButton').on('click', function() {
                loadTasks('dailyTasksAccordion-all');
            });

            $('#dailyTaskUserFilter').on('change', function() {
                applyUserFilter();
            });

            function initializeUserSelect() {
                const $select = $('#dailyTaskUserFilter');
                if (!$.fn.select2) return;
                if ($select.data('select2')) return;

                $select.select2({
                    placeholder: 'All team members',
                    width: '100%',
                    allowClear: true,
                    minimumResultsForSearch: 0
                });
            }

            function updateUserOptions(users) {
                const select = document.getElementById('dailyTaskUserFilter');
                const currentValue = select.value;
                const knownIds = new Set(Array.from(select.options).map(option => String(option.value)));

                users.forEach(user => {
                    const userId = String(user.id);
                    if (!knownIds.has(userId)) {
                        select.add(new Option(user.name || 'User', userId));
                        knownIds.add(userId);
                    }
                });

                select.value = currentValue;
                // A regular change event refreshes Select2's data adapter as
                // well as the user-card filter after dynamically adding options.
                $(select).trigger('change');
            }

            function applyUserFilter() {
                const selectedUserId = String($('#dailyTaskUserFilter').val() || '');
                $('.daily-task-user-card').each(function() {
                    const matches = !selectedUserId || String($(this).data('user-id')) === selectedUserId;
                    $(this).toggleClass('d-none', !matches);
                });
            }

            function renderTasks(users, accordionId) {

                let html = '';

                if (!users || users.length === 0) {
                    $('#' + accordionId).html(`
                        <div class="text-center text-muted py-3">
                            No users found
                        </div>
                    `);
                    return;
                }

                users.forEach((user, uIndex) => {

                    const tasks = Array.isArray(user.tasks) ? user.tasks : [];
                    let tasksRows = '';

                    if (tasks.length > 0) {
                        tasks.forEach((task, index) => {
                            const taskUrl = taskShowRoute.replace(':id', encodeURIComponent(task.id));
                            const projectUrl = task.project_page_id ? projectShowRoute.replace(':id', encodeURIComponent(task.project_page_id)) : '';
                            const storyUrl = task.story_page_id ? storyShowRoute.replace(':id', encodeURIComponent(task.story_page_id)) : '';
                            const description = plainText(task.description);
                            const status = task.status ? formatStatus(task.status) : '-';

                            tasksRows += `
                                <tr>
                                    <td data-label="#">${index + 1}</td>
                                    <td data-label="Task details" class="daily-task-title-cell">
                                        <a href="${taskUrl}" class="daily-task-title" target="_blank" rel="noopener noreferrer">${escapeHtml(task.title || '-')}</a>
                                        ${description ? `<span class="daily-task-description">${escapeHtml(description)}</span>` : ''}
                                    </td>
                                    <td data-label="Client">${escapeHtml(task.client_name || '-')}</td>
                                    <td data-label="Project">${task.project_name && projectUrl ? `<a href="${projectUrl}" class="daily-task-related-link" target="_blank" rel="noopener noreferrer">${escapeHtml(task.project_name)}</a>` : escapeHtml(task.project_name || '-')}</td>
                                    <td data-label="Story">${task.story_title && storyUrl ? `<a href="${storyUrl}" class="daily-task-related-link" target="_blank" rel="noopener noreferrer">${escapeHtml(task.story_title)}</a>` : escapeHtml(task.story_title || '-')}</td>
                                    <td data-label="Time consumed" class="daily-task-time">${escapeHtml(task.total_task_time || '00:00:00')}</td>
                                    <td data-label="Status"><span class="daily-task-status daily-task-status--${statusClass(task.status)}">${escapeHtml(status)}</span></td>
                                </tr>
                            `;
                        });
                    } else {
                        tasksRows = `
                            <tr>
                                <td colspan="7" class="daily-task-empty">No tasks were tracked for this user on the selected date.</td>
                            </tr>
                        `;
                    }

                    const bgColor = safeColor(user.performance?.background_color, '#8b1c1c');
                    const fontColor = safeColor(user.performance?.font_color, '#ffffff');
                    const performanceTitle = user.performance?.title ?? 'Absent';
                    const userName = escapeHtml(user.name || 'User');
                    const employeeId = escapeHtml(user.employee_id || '');
                    const totalTime = escapeHtml(user.total_time || '00:00:00');
                    const performanceLabel = escapeHtml(performanceTitle);

                    html += `
                    <div class="accordion-item daily-task-user-card" data-user-id="${escapeHtml(user.id)}">
                        <h2 class="accordion-header" id="heading-${uIndex}">
                            <button class="accordion-button collapsed daily-task-user-toggle" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse-${accordionId}-${uIndex}"
                                title="${performanceLabel}">
                                <span class="daily-task-performance-mark" style="background-color: ${bgColor};"></span>
                                <span class="daily-task-user-identity">
                                    <strong>${userName}</strong>
                                    ${employeeId ? `<small>${employeeId}</small>` : ''}
                                </span>
                                <span class="daily-task-user-metrics">
                                    <span><small>Tasks</small><strong>${tasks.length}</strong></span>
                                    <span><small>Time logged</small><strong>${totalTime}</strong></span>
                                </span>
                                <span class="daily-task-performance-badge" style="background-color: ${bgColor}; color: ${fontColor};">${performanceLabel}</span>
                            </button>
                        </h2>

                        <div id="collapse-${accordionId}-${uIndex}" class="accordion-collapse collapse"
                            data-bs-parent="#${accordionId}">

                            <div class="accordion-body daily-task-user-body">
                                <div class="table-responsive daily-task-table-wrap">
                                    <table class="table align-middle daily-task-table">
                                        <thead>
                                            <tr>
                                                <th class="daily-task-number">#</th>
                                                <th>Task details</th>
                                                <th>Client</th>
                                                <th>Project</th>
                                                <th>Story</th>
                                                <th>Time consumed</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${tasksRows}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                    `;
                });

                $('#' + accordionId).html(html);
                applyUserFilter();
            }

            $('#tagsTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function() {

                let tagId = $(this).data('tag-id'); // null for All
                let accordionId = $(this).data('accordion');

                // prevent reload if already loaded
                // if ($('#' + accordionId).html().trim() !== '') return;

                loadTasks(accordionId, tagId);

            });

            function formatStatus(status) {
                return status
                    .replaceAll('_', ' ')
                    .replace(/\b\w/g, char => char.toUpperCase());
            }

            function statusClass(status) {
                const knownStatuses = ['to_do', 'in_progress', 'pending', 'in_review', 'on_hold', 'closed', 'completed'];
                return knownStatuses.includes(status) ? status : 'unknown';
            }

            function safeColor(value, fallback) {
                return /^#[\da-f]{3,8}$/i.test(String(value || '')) ? value : fallback;
            }

        })
    </script>
@endpush
