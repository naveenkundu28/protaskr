@extends('layouts.app')

@section('title', isset($project) ? $project->name . ' - Tasks' : 'All Tasks')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/project-module.css') }}">
    <style>
        .inner_page_topbar h1 {
            white-space: nowrap;
            margin: 0;
            font-size: 2rem;
            font-weight: 750;
        }
        .inner_page_topbar .right_search_box {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .inner_page_topbar .search_box {
            position: relative;
            margin: 0;
        }
        .inner_page_topbar .search_box input {
            background: #fff;
            outline: none;
            border: 1px solid #ddd;
            padding: 10px 36px 10px 42px;
            font-size: 14px;
            min-width: 250px;
            border-radius: 10px;
            color: #010132;
            height: 42px;
            transition: all .15s ease;
        }
        .inner_page_topbar .search_box input:focus {
            border-color: #1469e8;
            box-shadow: 0 0 0 3px rgba(20, 105, 232, 0.12);
        }
        .inner_page_topbar .search_box button {
            position: absolute;
            left: 14px;
            top: 50% !important;
            transform: translateY(-50%) !important;
            background: transparent;
            border: 0;
            padding: 0;
            color: #64748b;
            cursor: pointer;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .search_clear_link {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            background: transparent;
            border: none;
            padding: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            text-decoration: none;
            border-radius: 50%;
            transition: all .15s ease;
        }
        .search_clear_link:hover {
            color: #0f172a;
            background: #f1f5f9;
        }
        .filter-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            height: 42px;
            padding: 0 16px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fff;
            color: #46536b;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            transition: all .15s ease;
        }
        .filter-toggle-btn:hover,
        .filter-toggle-btn:focus,
        .filter-toggle-btn[aria-expanded="true"] {
            background: #f8faff;
            border-color: #1469e8;
            color: #1469e8;
        }
        .filter-toggle-btn.has-filter {
            background: #eef5ff;
            border-color: #1469e8;
            color: #1469e8;
            font-weight: 600;
        }
        .filter-badge-count {
            background: #1469e8;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
            line-height: 1.2;
        }
        .toggle-chevron {
            font-size: 11px;
            color: #94a3b8;
            transition: transform .2s ease;
        }
        .filter-toggle-btn[aria-expanded="true"] .toggle-chevron {
            transform: rotate(180deg);
        }
        .pm-filter-panel {
            background: #fff;
            border: 1px solid #e6edf7;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 22px;
            box-shadow: 0 8px 24px rgba(20, 105, 232, 0.05);
        }
        .pm-filter-panel-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #46536b;
        }
        .pm-filter-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            margin-bottom: 6px;
            display: block;
        }
        .pm-filter-input {
            height: 42px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            color: #17243d;
            padding: 0 14px;
            background-color: #fff;
            transition: all .15s ease;
        }
        .pm-filter-input:focus {
            border-color: #1469e8;
            box-shadow: 0 0 0 3px rgba(20, 105, 232, 0.12);
            outline: none;
        }
        .pm-button {
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 10px !important;
            padding: 0 18px !important;
            font-weight: 600;
            white-space: nowrap;
        }
        .task-status-btn {
            border: 0;
            background: transparent;
            padding: 0;
            cursor: pointer;
        }
        .task-status-btn:focus {
            outline: none;
            box-shadow: none;
        }
        @media (max-width: 991px) {
            .inner_page_topbar {
                flex-direction: column;
                align-items: stretch;
                gap: 16px;
            }
            .inner_page_topbar .right_search_box {
                justify-content: flex-start;
            }
            .inner_page_topbar .search_box input {
                min-width: 100%;
            }
        }
    </style>
@endpush

@section('content')
<div class="container-fluid mt-80">
    {{-- Alerts --}}
    @session('success')
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ $value }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endsession

    {{-- Top heading & search/action bar --}}
    <div class="pm-heading">
        <div class="inner_page_topbar">
            <h1>{{ isset($project) ? $project->name . ' - Tasks' : 'Tasks' }}</h1>
            <div class="right_search_box">
                {{-- Search Box --}}
                <div class="search_box">
                    <button type="button" id="topbarSearchBtn" aria-label="Search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"></path>
                        </svg>
                    </button>
                    <input type="text" id="topbarSearchInput" value="{{ request('search') }}" placeholder="Search tasks...">
                    @if (request('search'))
                        <a href="{{ route('tasks.index', array_filter(['tab' => $currentTab, 'status' => request('status'), 'user' => request('user'), 'cdate' => request('cdate'), 'duedate' => request('duedate'), 'project_id' => optional($project)->id])) }}" class="search_clear_link" title="Clear search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>

                {{-- Filter Toggle Button --}}
                @php
                    $hasFilters = request('status') || request('user') || request('cdate') || request('duedate');
                    $activeFilterCount = (!empty(request('status')) ? 1 : 0)
                        + (!empty(request('user')) ? 1 : 0)
                        + (!empty(request('cdate')) ? 1 : 0)
                        + (!empty(request('duedate')) ? 1 : 0);
                @endphp
                <button type="button" class="filter-toggle-btn {{ $hasFilters ? 'has-filter' : '' }}"
                    data-bs-toggle="collapse" data-bs-target="#tasksFilterSection"
                    aria-expanded="{{ $hasFilters ? 'true' : 'false' }}" aria-controls="tasksFilterSection" id="filterToggleBtn">
                    <i class="bi bi-funnel{{ $hasFilters ? '-fill text-primary' : '' }}"></i>
                    <span>Filter</span>
                    @if ($hasFilters)
                        <span class="filter-badge-count">{{ $activeFilterCount }}</span>
                    @endif
                    <i class="bi bi-chevron-down toggle-chevron"></i>
                </button>

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

                {{-- Create Task Button --}}
                @can('tasks-create')
                    <a href="#" data-bs-toggle="modal" data-bs-target="#createTaskModal" class="btn btn-primary pm-button">
                        <i class="bi bi-plus-lg"></i> New Task
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Slide-down Filter Section --}}
    <div class="collapse {{ $hasFilters ? 'show' : '' }} mb-4" id="tasksFilterSection">
        <div class="pm-filter-panel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-primary"></i>
                    <span class="pm-filter-panel-title">Filter Tasks</span>
                </div>
                @if ($hasFilters || request('search'))
                    <a href="{{ route('tasks.index', array_filter(['tab' => $currentTab, 'project_id' => optional($project)->id])) }}" class="text-danger small text-decoration-none fw-semibold">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset All
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ route('tasks.index') }}" id="tasksFilterForm" class="m-0">
                @if ($project)
                    <input type="hidden" value="{{ $project->id }}" name="project_id">
                @endif
                <input type="hidden" name="tab" value="{{ $currentTab }}">
                <input type="hidden" name="search" id="filterHiddenSearch" value="{{ request('search') }}">

                <div class="row g-3 align-items-end">
                    {{-- Status Filter --}}
                    <div class="col-lg-3 col-md-6">
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
                    <div class="col-lg-3 col-md-6">
                        <label class="pm-filter-label" for="filter_user">Assigned To</label>
                        <select name="user" id="filter_user" class="form-select pm-filter-input" aria-label="Filter by user">
                            <option value="">All Employees</option>
                            @foreach ($project ? $project->users : $users as $user)
                                <option value="{{ $user->id }}" @selected(request('user') == $user->id)>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Created Date --}}
                    <div class="col-lg-2 col-md-4">
                        <label class="pm-filter-label" for="filter_cdate">Created Date</label>
                        <input id="filter_cdate" type="date" name="cdate" class="form-control pm-filter-input"
                            value="{{ request('cdate') }}">
                    </div>

                    {{-- Due Date --}}
                    <div class="col-lg-2 col-md-4">
                        <label class="pm-filter-label" for="filter_duedate">Due Date</label>
                        <input id="filter_duedate" type="date" name="duedate" class="form-control pm-filter-input"
                            value="{{ request('duedate') }}">
                    </div>

                    {{-- Action Buttons --}}
                    <div class="col-lg-2 col-md-4 d-flex gap-2">
                        <button type="submit" id="applyTasksFilterBtn" class="btn btn-primary pm-button w-100">
                            <i class="bi bi-funnel"></i> Apply
                        </button>
                        @if ($hasFilters)
                            <a href="{{ route('tasks.index', array_filter(['tab' => $currentTab, 'search' => request('search'), 'project_id' => optional($project)->id])) }}" class="btn btn-light border text-danger pm-button px-3" title="Clear Filters">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabs (Active, To Do, In Progress, In Review, On Hold, Completed) --}}
    <div class="pm-tabs">
        @php
            $tabList = [
                'active' => 'Active' . (isset($tabCounts['active']) ? ' (' . $tabCounts['active'] . ')' : ''),
                'to_do' => 'To Do' . (isset($tabCounts['to_do']) ? ' (' . $tabCounts['to_do'] . ')' : ''),
                'in_progress' => 'In Progress' . (isset($tabCounts['in_progress']) ? ' (' . $tabCounts['in_progress'] . ')' : ''),
                'in_review' => 'In Review' . (isset($tabCounts['in_review']) ? ' (' . $tabCounts['in_review'] . ')' : ''),
                'on_hold' => 'On Hold' . (isset($tabCounts['on_hold']) ? ' (' . $tabCounts['on_hold'] . ')' : ''),
                'completed' => 'Completed',
            ];
        @endphp
        @foreach ($tabList as $tabKey => $tabLabel)
            <a class="task-tab-{{ $tabKey }} {{ $currentTab == $tabKey ? 'active' : '' }}"
                href="{{ route('tasks.index', array_filter(['tab' => $tabKey, 'search' => request('search'), 'status' => request('status'), 'user' => request('user'), 'cdate' => request('cdate'), 'duedate' => request('duedate'), 'project_id' => optional($project)->id])) }}">
                {{ $tabLabel }}
            </a>
        @endforeach
    </div>

    {{-- Active filter tags (if search or filters applied) --}}
    @php
        $selectedUser = request('user') ? ($project ? $project->users->firstWhere('id', request('user')) : $users->firstWhere('id', request('user'))) : null;
        $statusConfig = config('site.tasks.status');
        $selectedStatusLabel = request('status') && isset($statusConfig[request('status')]) ? $statusConfig[request('status')] : null;
    @endphp
    @if (request('search') || request('status') || request('user') || request('cdate') || request('duedate'))
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <span class="text-muted small">Active Filters:</span>
            @if (request('search'))
                <span class="badge bg-light text-dark border py-1 px-2 d-inline-flex align-items-center gap-1">
                    Search: "{{ request('search') }}"
                    <a href="{{ route('tasks.index', array_filter(['tab' => $currentTab, 'status' => request('status'), 'user' => request('user'), 'cdate' => request('cdate'), 'duedate' => request('duedate'), 'project_id' => optional($project)->id])) }}" class="text-muted text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            @if ($selectedStatusLabel)
                <span class="badge bg-light text-primary border py-1 px-2 d-inline-flex align-items-center gap-1">
                    Status: {{ $selectedStatusLabel }}
                    <a href="{{ route('tasks.index', array_filter(['tab' => $currentTab, 'search' => request('search'), 'user' => request('user'), 'cdate' => request('cdate'), 'duedate' => request('duedate'), 'project_id' => optional($project)->id])) }}" class="text-primary text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            @if ($selectedUser)
                <span class="badge bg-light text-primary border py-1 px-2 d-inline-flex align-items-center gap-1">
                    User: {{ $selectedUser->name }}
                    <a href="{{ route('tasks.index', array_filter(['tab' => $currentTab, 'search' => request('search'), 'status' => request('status'), 'cdate' => request('cdate'), 'duedate' => request('duedate'), 'project_id' => optional($project)->id])) }}" class="text-primary text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            @if (request('cdate'))
                <span class="badge bg-light text-primary border py-1 px-2 d-inline-flex align-items-center gap-1">
                    Created: {{ \Carbon\Carbon::parse(request('cdate'))->format('d M Y') }}
                    <a href="{{ route('tasks.index', array_filter(['tab' => $currentTab, 'search' => request('search'), 'status' => request('status'), 'user' => request('user'), 'duedate' => request('duedate'), 'project_id' => optional($project)->id])) }}" class="text-primary text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            @if (request('duedate'))
                <span class="badge bg-light text-primary border py-1 px-2 d-inline-flex align-items-center gap-1">
                    Due: {{ \Carbon\Carbon::parse(request('duedate'))->format('d M Y') }}
                    <a href="{{ route('tasks.index', array_filter(['tab' => $currentTab, 'search' => request('search'), 'status' => request('status'), 'user' => request('user'), 'cdate' => request('cdate'), 'project_id' => optional($project)->id])) }}" class="text-primary text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            <a href="{{ route('tasks.index', array_filter(['tab' => $currentTab, 'project_id' => optional($project)->id])) }}" class="btn btn-link btn-sm text-danger p-0 text-decoration-none small ms-1">
                <i class="bi bi-x-circle"></i> Clear All
            </a>
        </div>
    @endif

    {{-- ================= TABLE CARD ================= --}}
    <div class="pm-card">
        <div class="table-responsive project_table">
            <table class="table pm-table align-middle">
                <thead>
                    <tr>
                        @can('tasks-bulk-complete')
                            <th style="width: 40px;"><input class="maincheck form-check-input" onclick="checkAll()" type="checkbox"></th>
                        @endcan
                        <th>#</th>
                        <th>Title</th>
                        <th>Project</th>
                        <th>Due Date</th>
                        <th>Assigned To</th>
                        <th>Time</th>
                        <th width="110px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr class="{{ $task->color }}">
                            @can('tasks-bulk-complete')
                                <td><input type="checkbox" class="check form-check-input" name="status[]" value="{{ $task->id }}"></td>
                            @endcan
                            <td>{{ $tasks->firstItem() + $loop->index }}</td>
                            <td>
                                <a class="pm-link fw-semibold" href="{{ route('tasks.show', $task->id) }}">
                                    {{ $task->title }}
                                </a>
                            </td>
                            <td>{{ $task->project?->name ?: '—' }}</td>
                            <td class="text-nowrap">{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : '—' }}</td>
                            <td class="text-nowrap">{{ $task->user->name ?? '—' }}</td>
                            <td class="text-nowrap font-monospace small">{{ $task->getTotalElapsedTime() }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="task-status-btn dropdown-toggle" type="button" id="dropdownMenuButton{{ $task->id }}"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        {!! $task->status_badge !!}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="dropdownMenuButton{{ $task->id }}">
                                        @foreach (config('site.tasks.status') as $key => $value)
                                            @if ($key != 'closed')
                                                <li>
                                                    <form action="{{ route('tasks.status.update', $task->id) }}" method="POST" class="mb-0">
                                                        @csrf
                                                        <input type="hidden" name="status" value="{{ $key }}">
                                                        <button type="submit" class="dropdown-item {{ $task->status === $key ? 'active' : '' }}">
                                                            {{ $value }}
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ optional(auth()->user())->can('tasks-bulk-complete') ? 8 : 7 }}" class="text-center py-5 text-muted">
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
    <x-task-modal :project="$project" />

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
            const $filterForm = $('#tasksFilterForm');
            const $topbarSearch = $('#topbarSearchInput');
            const $hiddenSearch = $('#filterHiddenSearch');

            // Sync topbar search to filter form
            $topbarSearch.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $hiddenSearch.val($(this).val());
                    $filterForm.submit();
                }
            });

            $('#topbarSearchBtn').on('click', function() {
                $hiddenSearch.val($topbarSearch.val());
                $filterForm.submit();
            });

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
                        alert('Please select at least one task.');
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
                            alert('Tasks marked as complete successfully!');
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
