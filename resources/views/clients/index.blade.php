@extends('layouts.app')

@section('title', 'Clients')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/project-module.css') }}">
    <style>
        .inner_page_topbar .right_search_box {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            flex-wrap: nowrap;
        }
        .clients_search_filter_form {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            flex-wrap: nowrap;
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
            min-width: 290px;
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
        .filter-dropdown-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 42px;
            padding: 0 14px;
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
        .filter-dropdown-btn:hover,
        .filter-dropdown-btn:focus,
        .filter-dropdown-btn[aria-expanded="true"] {
            background: #f8faff;
            border-color: #1469e8;
            color: #1469e8;
        }
        .filter-dropdown-btn.has-filter {
            background: #eef5ff;
            border-color: #1469e8;
            color: #1469e8;
            font-weight: 600;
        }
        .filter-menu {
            min-width: 250px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
            padding: 10px;
            margin-top: 6px !important;
        }
        .filter-menu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 6px;
        }
        .filter-menu-title {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .filter-item-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 8px;
            border-radius: 6px;
            cursor: pointer;
            margin: 0;
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            transition: background .15s ease;
        }
        .filter-item-row:hover {
            background: #f8fafc;
        }
        .filter-item-row input[type="checkbox"] {
            margin: 0;
            cursor: pointer;
            width: 16px;
            height: 16px;
        }
        .filter-tag-pill {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: auto;
            font-weight: 600;
        }
        .filter-tag-internal_team,
        .filter-tag-external_team {
            background: #f1f5f9;
            color: #475569;
        }
        .filter-tag-project {
            background: #fff3e0;
            color: #b45309;
        }
        .filter-tag-client {
            background: #eef2ff;
            color: #3b82f6;
        }
        .filter-menu-footer {
            padding-top: 8px;
            border-top: 1px solid #f1f5f9;
            margin-top: 6px;
        }
        .filter-apply-btn {
            height: 34px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
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
        .type-cell-neutral {
            background-color: #f8fafc !important;
        }
        .type-cell-project {
            background-color: #fffaf4 !important;
        }
        .client-type-tag {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: capitalize;
            display: block;
            margin-top: 2px;
        }
        .clients-table-nowrap {
            white-space: nowrap;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid mt-80">
    {{-- Alerts --}}

    {{-- Top heading & search bar --}}
    <div class="pm-heading">
        <div class="inner_page_topbar">
            <h1>{{ request('type') == 'archived' ? 'Archived Clients' : 'Clients' }}</h1>
            <div class="right_search_box">
                <form method="GET" action="{{ route('clients.index') }}" class="clients_search_filter_form" id="clientsFilterForm">
                    @if (request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif

                    {{-- Search Input (Identical to Projects listing) --}}
                    <div class="search_box">
                        <button type="submit" aria-label="Search">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"></path>
                            </svg>
                        </button>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Clients...">
                        @if (request('search'))
                            <a href="{{ route('clients.index', array_filter(['type' => request('type'), 'type_filter' => request('type_filter')])) }}" class="search_clear_link" title="Clear search">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>

                    {{-- Type Filter Dropdown --}}
                    @php
                        $activeTypes = array_values(array_filter((array) request('type_filter', [])));
                        $typeMap = [
                            'internal_team' => 'Internal Team',
                            'external_team' => 'External Team',
                            'client' => 'Client',
                            'project' => 'Project',
                        ];
                    @endphp
                    <div class="dropdown filter-dropdown">
                        <button class="filter-dropdown-btn {{ !empty($activeTypes) ? 'has-filter' : '' }}" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <i class="bi {{ !empty($activeTypes) ? 'bi-funnel-fill text-primary' : 'bi-funnel' }}"></i>
                            <span>
                                @if (empty($activeTypes))
                                    All Types
                                @elseif (count($activeTypes) === 1)
                                    {{ $typeMap[$activeTypes[0]] ?? 'Type' }}
                                @else
                                    Types ({{ count($activeTypes) }})
                                @endif
                            </span>
                            <i class="bi bi-chevron-down ms-1" style="font-size: 11px;"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end filter-menu">
                            <div class="filter-menu-header">
                                <span class="filter-menu-title">Client Type</span>
                                @if (!empty($activeTypes))
                                    <a href="{{ route('clients.index', array_filter(['type' => request('type'), 'search' => request('search')])) }}" class="text-danger small text-decoration-none fw-semibold">
                                        Reset
                                    </a>
                                @endif
                            </div>
                            <div class="py-1">
                                @foreach ($typeMap as $val => $lbl)
                                    <label class="filter-item-row" for="type_filter_{{ $val }}">
                                        <input class="form-check-input" type="checkbox" name="type_filter[]" value="{{ $val }}" id="type_filter_{{ $val }}"
                                            {{ in_array($val, $activeTypes) ? 'checked' : '' }}>
                                        <span>{{ $lbl }}</span>
                                        <span class="filter-tag-pill filter-tag-{{ $val }}">{{ str_replace('_', ' ', $val) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="filter-menu-footer">
                                <button type="submit" class="btn btn-primary btn-sm w-100 filter-apply-btn">
                                    Apply Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Add Client Button --}}
                @if (!request('type'))
                    @can('clients-create')
                        <a class="btn btn-primary pm-button" href="{{ route('clients.create') }}">
                            <i class="bi bi-plus-lg"></i> Add Client
                        </a>
                    @endcan
                @endif
            </div>
        </div>
    </div>

    {{-- Tabs (Ongoing, Completed, Archived) --}}
    <div class="pm-tabs">
        <a class="{{ !request('type') ? 'active' : '' }}"
            href="{{ route('clients.index', array_filter(['search' => request('search'), 'type_filter' => request('type_filter')])) }}">
            Ongoing
        </a>
        <a class="{{ request('type') == 'completed' ? 'active' : '' }}"
            href="{{ route('clients.index', array_filter(['type' => 'completed', 'search' => request('search'), 'type_filter' => request('type_filter')])) }}">
            Completed
        </a>
        <a class="{{ request('type') == 'archived' ? 'active' : '' }}"
            href="{{ route('clients.index', array_filter(['type' => 'archived', 'search' => request('search'), 'type_filter' => request('type_filter')])) }}">
            Archived
        </a>
    </div>

    {{-- Active filter tags (if search or type applied) --}}
    @if (request('search') || !empty(request('type_filter')))
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <span class="text-muted small">Active Filters:</span>
            @if (request('search'))
                <span class="badge bg-light text-dark border py-1 px-2 d-inline-flex align-items-center gap-1">
                    Search: "{{ request('search') }}"
                    <a href="{{ route('clients.index', array_filter(['type' => request('type'), 'type_filter' => request('type_filter')])) }}" class="text-muted text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            @if (!empty(request('type_filter')))
                @foreach ((array) request('type_filter') as $tf)
                    <span class="badge bg-light text-primary border py-1 px-2 d-inline-flex align-items-center gap-1">
                        Type: {{ ucwords(str_replace('_', ' ', $tf)) }}
                        <a href="{{ route('clients.index', array_filter(['type' => request('type'), 'search' => request('search'), 'type_filter' => array_values(array_diff((array) request('type_filter'), [$tf]))])) }}" class="text-primary text-decoration-none ms-1">&times;</a>
                    </span>
                @endforeach
            @endif
            <a href="{{ route('clients.index', request()->only('type')) }}" class="btn btn-link btn-sm text-danger p-0 text-decoration-none small ms-1">
                <i class="bi bi-x-circle"></i> Clear All
            </a>
        </div>
    @endif

    {{-- Table Card --}}
    <div class="pm-card">
        <div class="table-responsive project_table">
            <table class="table pm-table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client / Company</th>
                        <th>Assigned Resources</th>
                        <th>Billable Users</th>
                        <th class="clients-table-nowrap">Service Start Date</th>
                        <th class="clients-table-nowrap">Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        @php
                            $rowTypeClass = in_array($project->type, ['internal_team', 'external_team'], true)
                                ? 'type-cell-neutral'
                                : ($project->type === 'project' ? 'type-cell-project' : '');
                        @endphp
                        <tr class="{{ $rowTypeClass }}">
                            <td>{{ $projects->firstItem() + $loop->index }}</td>
                            <td>
                                <a class="pm-link fw-semibold" href="{{ route('clients.show', $project->id) }}">
                                    {{ $project->name }}{{ $project->company_name ? ' (' . $project->company_name . ')' : '' }}
                                </a>
                                @if ($project->type)
                                    <small class="client-type-tag">
                                        {{ str_replace('_', ' ', $project->type) }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if ($project->assigned_resource_names->isNotEmpty())
                                    <div class="assigned-resource-list">
                                        @foreach ($project->assigned_resource_names as $resourceName)
                                            @php
                                                $nameParts = preg_split('/\\s+/', trim($resourceName));
                                                $initials = strtoupper(
                                                    substr($nameParts[0], 0, 1) .
                                                    (count($nameParts) > 1
                                                        ? substr($nameParts[count($nameParts) - 1], 0, 1)
                                                        : '')
                                                );
                                            @endphp

                                            <span class="assigned-resource">
                                                <span class="assigned-resource-avatar">{{ $initials }}</span>
                                                <span>{{ $resourceName }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($project->billable_resource_names->isNotEmpty())
                                    <div class="assigned-resource-list">
                                        @foreach ($project->billable_resource_names as $resourceName)
                                            @php
                                                $nameParts = preg_split('/\s+/', trim($resourceName));
                                                $initials = strtoupper(
                                                    substr($nameParts[0], 0, 1) .
                                                    (count($nameParts) > 1
                                                        ? substr($nameParts[count($nameParts) - 1], 0, 1)
                                                        : '')
                                                );
                                            @endphp

                                            <span class="assigned-resource">
                                                <span class="assigned-resource-avatar">{{ $initials }}</span>
                                                <span>{{ $resourceName }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            {{-- <td>
                                @isset($project->users)
                                    @php
                                        $users = $project->users;
                                        $visibleUsers = $users->take(2);
                                        $remainingUsers = $users->slice(2);
                                        $remainingCount = $users->count() - 2;
                                    @endphp
                                    <div class="d-flex align-items-center flex-wrap gap-1">
                                        @foreach ($visibleUsers as $user)
                                            <span class="{{ $user->trashed() ? 'text-danger' : '' }}">
                                                {{ $user->name }}
                                            </span>{{ !$loop->last ? ',' : '' }}
                                        @endforeach
                                        @if ($remainingCount > 0)
                                            <span class="badge rounded-pill bg-light text-primary border" data-bs-toggle="tooltip" style="cursor: pointer;"
                                                title="@foreach ($remainingUsers as $user){{ $user->name }}{{ !$loop->last ? ', ' : '' }}@endforeach">
                                                +{{ $remainingCount }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    —
                                @endisset
                            </td> --}}
                            {{-- <td>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '—' }}</td> --}}
                            {{-- <td>
                                <span class="fw-semibold">{{ number_format((float) ($project->current_month_hours ?? 0), 2) }}</span>
                            </td> --}}
                            <td class="clients-table-nowrap">
                                {{ $project->latest_service_start_date
                                    ? \Carbon\Carbon::parse($project->latest_service_start_date)->format('j F Y')
                                    : '—' }}
                            </td>
                            <td class="clients-table-nowrap">
                                <span class="pm-badge pm-status-{{ $project->status }} {{ $project->status }}">
                                    {{ str_replace('_', ' ', ucfirst($project->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="project-actions">
                                    <button type="button" class="emp_status_action" aria-label="Client actions">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul>
                                        @if (request('type') == 'archived')
                                            @can('clients-delete')
                                                <li>
                                                    <form action="{{ route('clients.restore', $project->id) }}" method="POST" class="mb-0">
                                                        @csrf
                                                        <button type="submit" class="project-delete-action text-success"
                                                            onclick="return confirm('Restore this client?')">
                                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                                        </button>
                                                    </form>
                                                </li>
                                            @endcan
                                        @else
                                            {{-- @can('tasks-access')
                                                <li>
                                                    <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}">
                                                        <i class="bi bi-list-check me-1"></i> Tasks
                                                    </a>
                                                </li>
                                            @endcan --}}
                                            @can('clients-services')
                                                <li>
                                                    <a href="{{ route('clients.services.index', $project->id) }}">
                                                        <i class="bi bi-gear me-1"></i> Services
                                                    </a>
                                                </li>
                                            @endcan
                                            {{-- @can('clients-show')
                                                <li>
                                                    <a href="{{ route('clients.show', $project->id) }}">
                                                        <i class="bi bi-eye me-1"></i> View
                                                    </a>
                                                </li>
                                            @endcan --}}
                                            @if ($project->profile_user_id)
                                                @canany(['clients-profile-create', 'clients-profile-update', 'clients-profile-delete'])
                                                    <li>
                                                        <a href="{{ route('clients.profile.show', $project->id) }}">
                                                            <i class="bi bi-person-badge me-1"></i> Profile
                                                        </a>
                                                    </li>
                                                @endcanany
                                            @else
                                                @can('clients-profile-create')
                                                    <li>
                                                        <form action="{{ route('clients.profile.store', $project->id) }}" method="POST" class="mb-0">
                                                            @csrf
                                                            <button type="submit" class="project-delete-action text-primary"
                                                                onclick="return confirm('Create a login profile for this client?')">
                                                                <i class="bi bi-person-plus me-1"></i> Create Profile
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endcan
                                            @endif
                                            @can('clients-update')
                                                <li>
                                                    <a href="{{ route('clients.edit', $project->id) }}">
                                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                                    </a>
                                                </li>
                                                @if ($project->status !== 'completed')
                                                    <li>
                                                        <button type="button" class="project-delete-action text-success"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#completeClientModal"
                                                            data-client-id="{{ $project->id }}"
                                                            data-client-name="{{ $project->name }}"
                                                            data-start-date="{{ $project->start_date }}">
                                                            <i class="bi bi-check-circle me-1"></i> Mark Completed
                                                        </button>
                                                    </li>
                                                @endif
                                            @endcan
                                            @can('clients-delete')
                                                <li>
                                                    <form action="{{ route('clients.destroy', $project->id) }}" method="POST" class="mb-0"
                                                        onsubmit="return confirm('Delete this client?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="project-delete-action text-danger" type="submit">
                                                            <i class="bi bi-trash me-1"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            @endcan
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                No clients match your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination matching Projects & Clients layout --}}
            {{ $projects->links('pagination.pm', ['entity' => 'Clients']) }}
        </div>
    </div>

    {{-- Type Legend --}}
    <div class="d-flex align-items-center gap-2 flex-wrap mt-3 mb-4 ps-1">
        <span class="text-muted small fw-semibold">Legend:</span>
        <span class="badge px-2 py-1" style="background: #f1f3f5; color: #495057; border: 1px solid #ced4da;">Internal Team</span>
        <span class="badge px-2 py-1" style="background: #f1f3f5; color: #495057; border: 1px solid #ced4da;">External Team</span>
        <span class="badge px-2 py-1" style="background: #fff3e0; color: #8a4b08; border: 1px solid #ffd8a8;">Project</span>
        <span class="badge bg-white text-dark px-2 py-1" style="border: 1px solid #ced4da;">Client</span>
    </div>
</div>

{{-- Complete Client Modal --}}
<div class="modal fade" id="completeClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="completeClientForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Complete Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Set end date for <strong id="completeClientName"></strong>.</p>
                <div class="mb-0">
                    <label for="complete_end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="end_date" id="complete_end_date" required>
                    <small class="text-muted" id="completeDateHint"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Save & Complete</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('completeClientModal');
        if (modal) {
            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const clientId = button.getAttribute('data-client-id');
                const clientName = button.getAttribute('data-client-name');
                const startDate = button.getAttribute('data-start-date');

                const form = document.getElementById('completeClientForm');
                const nameEl = document.getElementById('completeClientName');
                const endDateEl = document.getElementById('complete_end_date');
                const hintEl = document.getElementById('completeDateHint');

                form.action = "{{ route('clients.complete', ':id') }}".replace(':id', clientId);
                nameEl.textContent = clientName || 'this client';
                endDateEl.value = '';

                if (startDate) {
                    endDateEl.min = startDate;
                    hintEl.textContent = "End date must be on or after " + startDate + ".";
                } else {
                    endDateEl.removeAttribute('min');
                    hintEl.textContent = '';
                }
            });
        }
    });
</script>
@endsection
