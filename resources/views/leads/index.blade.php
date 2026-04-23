@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div style="padding: 0px 12px;">
            <div class="row">
                <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <h2 class="mb-0">Leads Management</h2>

                        <form method="GET"
                            action="{{ route('leads.index') }}"
                            class="d-flex gap-2 auto-search-form" data-search-target="#tableSearchResults">
                            <input type="hidden" name="date_filter" value="{{ $current_filter }}">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="Search leads">

                            {{-- <button type="submit" class="btn btn-primary">Search</button> --}}
                        </form>
                    </div>

                    <a href="{{ route('leads.create') }}" class="btn btn-primary">Create New Lead</a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Date Filter Tabs -->
        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $current_filter == 'today' ? 'active' : '' }}"
                    href="{{ route('leads.index', ['date_filter' => 'today', 'search' => request('search')]) }}">Today</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $current_filter == 'yesterday' ? 'active' : '' }}"
                    href="{{ route('leads.index', ['date_filter' => 'yesterday', 'search' => request('search')]) }}">Yesterday</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $current_filter == 'current_week' ? 'active' : '' }}"
                    href="{{ route('leads.index', ['date_filter' => 'current_week', 'search' => request('search')]) }}">Current Week</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $current_filter == 'last_week' ? 'active' : '' }}"
                    href="{{ route('leads.index', ['date_filter' => 'last_week', 'search' => request('search')]) }}">Last
                    Week</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $current_filter == 'last_15_days' ? 'active' : '' }}"
                    href="{{ route('leads.index', ['date_filter' => 'last_15_days', 'search' => request('search')]) }}">Last
                    15 Days</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $current_filter == 'last_month' ? 'active' : '' }}"
                    href="{{ route('leads.index', ['date_filter' => 'last_month', 'search' => request('search')]) }}">Last
                    Month</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $current_filter == 'all' ? 'active' : '' }}"
                    href="{{ route('leads.index', ['date_filter' => 'all', 'search' => request('search')]) }}">All
                    Leads</a>
            </li>
        </ul>

        <div id="tableSearchResults">

            <div class="table-responsive">
                <table class="table table-bordered table-sm text-nowrap">
                    <tr>
                        <th>No</th>
                        <th>Lead</th>
                        <th>Status</th>
                        <th>TL</th>
                        <th>Team</th>
                        <th>Requirement</th>
                        <th>Technologies</th>
                        <th>Claimed Group</th>
                        <th>Is Call Done</th>
                        <th>CAM Member</th>
                        <th>Date</th>
                        {{-- <th width="250px">JD</th> --}}
                        <th>Action</th>
                    </tr>

                    @forelse ($leads as $key => $lead)
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td>{{ $lead->lead }}</td>
                            {{-- Latest Status --}}
                            <td>
                                {{ optional(optional($lead->latestNotesHistory)->leadStatus)->name
                                    ? ucwords(str_replace('_', ' ', optional(optional($lead->latestNotesHistory)->leadStatus)->name))
                                    : '' }}
                            </td>
                            <td>{{ $lead->tlUser->name ?? '' }}</td>
                            <td>{{ $lead->teamUser->name ?? '' }}</td>
                            <td>{{ ucwords(str_replace('_', '-', $lead->requirement->requirement ?? '')) }}</td>
                            {{-- <td>
                            @if ($lead->skills->count())
                                {{ $lead->skills->pluck('name')->join(', ') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td> --}}
                            <td>
                                @if ($lead->skills->count())
                                    @php
                                        $skills = $lead->skills;
                                        $visibleSkills = $skills->take(2);
                                        $remainingSkills = $skills->slice(2);
                                        $remainingCount = $skills->count() - 2;
                                    @endphp

                                    <span>
                                        {{-- Visible Skills --}}
                                        @foreach ($visibleSkills as $skill)
                                            <span>{{ $skill->name }}</span>{{ !$loop->last ? ',' : '' }}
                                        @endforeach

                                        {{-- Remaining Count --}}
                                        @if ($remainingCount > 0)
                                            <span class="text-primary" data-bs-toggle="tooltip" style="cursor: pointer;"
                                                title="@foreach ($remainingSkills as $skill){{ $skill->name }}{{ !$loop->last ? ', ' : '' }} @endforeach">
                                                +{{ $remainingCount }}
                                            </span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $lead->claimed_group }}</td>

                            <td>{{ $lead->call_done ? 'Yes' : 'No' }}</td>
                            <td>{{ $lead->cam_member }}</td>
                            <td>
                                {{ !empty($lead->date) ? \Carbon\Carbon::parse($lead->date)->format('d-m-Y') : '' }}
                            </td>
                            {{-- <td class="jd-column">{!! $lead->jd !!}</td> --}}
                            {{-- <td>{{ $lead->notesHistories->notes ?? '' }}</td> --}}
                            {{-- <td class="jd-column">
                            {{ optional($lead->latestNotesHistory)->notes }}
                        </td> --}}

                            <td>
                                <div class="dropdown position-static">
                                    <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bi bi-list-task"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">

                                        <li>
                                            <a class="dropdown-item text-warning"
                                                href="{{ route('leads.notes-histories.index', $lead->id) }}">Notes
                                                Histories</a>
                                        </li>

                                        <li>
                                            <a class="dropdown-item text-primary"
                                                href="{{ route('leads.edit', $lead->id) }}">Edit</a>
                                        </li>

                                        <li>
                                            <form method="POST" action="{{ route('leads.destroy', $lead->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="dropdown-item text-danger"
                                                    onclick="return confirm('Are You Sure You Want to Delete This lead?')">Delete</button>
                                            </form>
                                        </li>

                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">
                                No Leads Found
                            </td>
                        </tr>
                    @endforelse
                </table>
            </div>

            {!! $leads->links('pagination::bootstrap-5') !!}

        </div>
    </div>
@endsection
