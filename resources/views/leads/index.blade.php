@extends('layouts.app')

@section('title', 'Leads')

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
        @media (max-width: 768px) {
            .inner_page_topbar {
                flex-direction: column;
                align-items: stretch;
                gap: 16px;
            }
            .inner_page_topbar .right_search_box {
                flex-wrap: wrap;
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

    {{-- Top heading & search bar --}}
    <div class="pm-heading">
        <div class="inner_page_topbar">
            <h1>Leads</h1>
            <div class="right_search_box">
                <form method="GET" action="{{ route('leads.index') }}" class="auto-search-form m-0" data-search-target="#tableSearchResults">
                    <input type="hidden" name="date_filter" value="{{ $current_filter }}">

                    {{-- Search Input (Identical to Projects & Clients listing) --}}
                    <div class="search_box">
                        <button type="submit" aria-label="Search">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"></path>
                            </svg>
                        </button>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search leads...">
                        @if (request('search'))
                            <a href="{{ route('leads.index', ['date_filter' => $current_filter]) }}" class="search_clear_link" title="Clear search">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </form>

                {{-- Create Lead Button --}}
                @can('leads-create')
                    <a href="{{ route('leads.create') }}" class="btn btn-primary pm-button">
                        <i class="bi bi-plus-lg"></i> Create Lead
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Date Filter Tabs --}}
    <div class="pm-tabs">
        @php
            $tabs = [
                'today' => 'Today',
                'yesterday' => 'Yesterday',
                'current_week' => 'Current Week',
                'last_week' => 'Last Week',
                'last_15_days' => 'Last 15 Days',
                'last_month' => 'Last Month',
                'all' => 'All Leads',
            ];
        @endphp
        @foreach ($tabs as $key => $label)
            <a class="{{ $current_filter == $key ? 'active' : '' }}"
                href="{{ route('leads.index', array_filter(['date_filter' => $key, 'search' => request('search')])) }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Active filter tag (if search applied) --}}
    @if (request('search'))
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="text-muted small">Active Search:</span>
            <span class="badge bg-light text-dark border py-1 px-2 d-inline-flex align-items-center gap-1">
                "{{ request('search') }}"
                <a href="{{ route('leads.index', ['date_filter' => $current_filter]) }}" class="text-muted text-decoration-none ms-1">&times;</a>
            </span>
        </div>
    @endif

    {{-- ================= AJAX TABLE CONTAINER ================= --}}
    <div id="tableSearchResults">
        <div class="pm-card">
            <div class="table-responsive project_table">
                <table class="table pm-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Lead</th>
                            <th>Status</th>
                            <th>TL</th>
                            <th>Team</th>
                            <th>Requirement</th>
                            <th>Technologies</th>
                            <th>Claimed Group</th>
                            <th>Call Done</th>
                            <th>CAM Member</th>
                            <th>Lead Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            <tr>
                                <td>{{ $leads->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $lead->lead }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusName = optional(optional($lead->latestNotesHistory)->leadStatus)->name;
                                    @endphp
                                    @if ($statusName)
                                        <span class="pm-badge {{ strtolower(str_replace(' ', '_', $statusName)) }}">
                                            {{ ucwords(str_replace('_', ' ', $statusName)) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $lead->tlUser->name ?? '—' }}</td>
                                <td>{{ $lead->teamUser->name ?? '—' }}</td>
                                <td>
                                    @if ($lead->requirement)
                                        <span class="badge bg-light text-dark border">
                                            {{ ucwords(str_replace('_', '-', $lead->requirement->requirement)) }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if ($lead->skills->count())
                                        @php
                                            $skills = $lead->skills;
                                            $visibleSkills = $skills->take(2);
                                            $remainingSkills = $skills->slice(2);
                                            $remainingCount = $skills->count() - 2;
                                        @endphp
                                        <div class="d-flex align-items-center flex-wrap gap-1">
                                            @foreach ($visibleSkills as $skill)
                                                <span class="badge bg-light text-secondary border">{{ $skill->name }}</span>
                                            @endforeach
                                            @if ($remainingCount > 0)
                                                <span class="badge rounded-pill bg-light text-primary border" data-bs-toggle="tooltip" style="cursor: pointer;"
                                                    title="@foreach ($remainingSkills as $skill){{ $skill->name }}{{ !$loop->last ? ', ' : '' }} @endforeach">
                                                    +{{ $remainingCount }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase">{{ $lead->claimed_group }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $lead->call_done ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }}">
                                        {{ $lead->call_done ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>{{ $lead->cam_member ?: '—' }}</td>
                                <td>
                                    {{ !empty($lead->date) ? \Carbon\Carbon::parse($lead->date)->format('d M Y') : '—' }}
                                </td>
                                <td>
                                    <div class="project-actions">
                                        <button type="button" class="emp_status_action" aria-label="Lead actions">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul>
                                            <li>
                                                <a href="{{ route('leads.notes-histories.index', $lead->id) }}">
                                                    <i class="bi bi-journal-text me-1 text-warning"></i> Notes
                                                </a>
                                            </li>
                                            @can('leads-update')
                                                <li>
                                                    <a href="{{ route('leads.edit', $lead->id) }}">
                                                        <i class="bi bi-pencil-square me-1 text-primary"></i> Edit
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('leads-delete')
                                                <li>
                                                    <form method="POST" action="{{ route('leads.destroy', $lead->id) }}" class="mb-0"
                                                        onsubmit="return confirm('Delete this lead?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="project-delete-action text-danger">
                                                            <i class="bi bi-trash me-1"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">
                                    No leads found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination matching Projects & Clients layout --}}
            {{ $leads->links('pagination.pm', ['entity' => 'Leads']) }}
        </div>
    </div>
</div>
@endsection
