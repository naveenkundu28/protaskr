@extends('layouts.app')
@section('title')
    Dashboard
@endsection
@section('content')
    <div class="container">
        <h2>VE Emp Tracker</h2>

        <div class="row mb-4">
            @if (!Auth::user()->hasRole('Admin'))
                <div class="col-md-3 mb-4">
                    <div class="card shadow-sm h-100">

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Tasks</h5>
                            <p class="card-text flex-grow-1">You have <strong>{{ $tasksCount }}</strong> tasks pending.</p>
                            <a href="{{ route('clients.index') }}" class="btn btn-primary mt-auto">View Tasks</a>
                        </div>
                    </div>
                </div>
            @endif

            @if (Auth::user()->hasRole('Admin'))
                <div class="col-sm-6 col-md-3 col-lg-2 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title f-16 mb-1">Tags Summary</h5>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0 align-middle f-14">
                                    <tbody>
                                        @forelse($tagSummary as $index => $tag)
                                            @php
                                                $tagColor = $tag->color ?: '#6c757d';
                                                $hex = ltrim($tagColor, '#');
                                                if (strlen($hex) === 3) {
                                                    $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
                                                }
                                                $r = hexdec(substr($hex, 0, 2));
                                                $g = hexdec(substr($hex, 2, 2));
                                                $b = hexdec(substr($hex, 4, 2));
                                                $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
                                                $textColor = $brightness > 140 ? '#111111' : '#ffffff';
                                            @endphp
                                            <tr>
                                                <td
                                                    style="font-weight: 600; background-color: {{ $tagColor }}; color: {{ $textColor }};">
                                                    {{ strtoupper($tag->name) }}
                                                </td>
                                                <td class="text-center fw-semibold">
                                                    <a href="{{ route('users.listing', ['tag_id' => $tag->id, 'tag_scope' => 'internal']) }}"
                                                        class="text-decoration-none">
                                                        {{ $tag->users_count }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center text-muted">No tags found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title f-16 mb-1">Work Type</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0 align-middle f-14 text-center">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Number of Users</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($workingHoursSummary as $label => $count)
                                            <tr>
                                                <td>{{ $label }}</td>
                                                <td class="fw-semibold text-primary">
                                                    <a href="{{ route('users.listing', ['work_type' => $label]) }}"
                                                        class="text-decoration-none">
                                                        {{ $count }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-muted small mb-0 f-12 mt-2">
                                {{ $now->format('F Y') }}@if ($masterHours)
                                    , master hours: {{ $masterHours }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title f-16 mb-1">Profit / Loss Summary</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0 align-middle text-center f-14">
                                    <tbody>
                                        <tr>
                                            <td class="p-1">
                                                <div class="fs-6 fw-bold">
                                                    {{ number_format($profitLossSummary['total_hours'], 0) }}</div>
                                                <div class="text-muted small f-12">Total Hours</div>
                                                <div class="small f-12">{{ $profitLossSummary['internal_users_count'] }}
                                                    Internal Users</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="p-1">
                                                <div class="fs-6 fw-bold">
                                                    {{ number_format($profitLossSummary['assigned_hours'], 0) }}</div>
                                                <div class="text-muted small f-12">Assigned Hours</div>
                                                <div class="small f-12">Current Month Services</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            @if ($profitLossSummary['loss_percentage'] != 0)
                                                <td class="p-1">
                                                    <div class="fs-6 fw-bold text-danger">
                                                        <i
                                                            class="bi bi-graph-down-arrow me-1"></i>{{ $profitLossSummary['loss_percentage'] }}%
                                                    </div>
                                                    <div class="text-muted small f-12">Loss</div>
                                                    <div class="small text-danger f-12">
                                                        {{ number_format($profitLossSummary['loss_hours'], 0) }} hrs</div>
                                                </td>
                                            @else
                                                <td class="p-1">
                                                    <div class="fs-6 fw-bold text-success">
                                                        <i
                                                            class="bi bi-graph-up-arrow me-1"></i>{{ $profitLossSummary['profit_percentage'] }}%
                                                    </div>
                                                    <div class="text-muted small f-12">Profit</div>
                                                    <div class="small text-success f-12">
                                                        {{ number_format($profitLossSummary['profit_hours'], 0) }} hrs
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-muted f-12 mb-0 mt-1">
                                Based on {{ $now->format('F Y') }} master hours and internal user capacity.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if (Auth::user()->hasRole('Admin'))
            <div class="row mb-4">
                <div class="col-12 col-lg-5 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <ul class="nav nav-tabs mb-3" id="skillBenchTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="skills-set-tab" data-bs-toggle="tab"
                                        data-bs-target="#skills-set-pane" type="button" role="tab"
                                        aria-controls="skills-set-pane" aria-selected="true">Skills Set</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link color-bench" id="bench-users-tab" data-bs-toggle="tab"
                                        data-bs-target="#bench-users-pane" type="button" role="tab"
                                        aria-controls="bench-users-pane" aria-selected="false">Bench</button>
                                </li>
                            </ul>

                            <div class="mb-3">
                                <input type="text" class="form-control form-control-sm" id="skillBenchSearch"
                                    placeholder="Search skills or users">
                            </div>

                            <div class="tab-content" id="skillBenchTabsContent">
                                <div class="tab-pane fade show active" id="skills-set-pane" role="tabpanel"
                                    aria-labelledby="skills-set-tab" tabindex="0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm align-middle f-14 mb-0"
                                            id="skillsSetTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Skills</th>
                                                    <th>Name</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($skillSetSummary as $index => $skill)
                                                    <tr class="skill-bench-row"
                                                        data-search="{{ strtolower($skill['name'] . ' ' . collect($skill['users'])->pluck('name')->join(' ')) }}">

                                                        <td>{{ $index + 1 }}</td>

                                                        <td>{{ $skill['name'] }}</td>

                                                        <td>
                                                            {{ collect($skill['users'])->map(fn($u) => $u->name . ' - ' . $u->employee_id)->join(', ') }}
                                                        </td>

                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">No primary skill
                                                            users found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="bench-users-pane" role="tabpanel"
                                    aria-labelledby="bench-users-tab" tabindex="0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm align-middle f-14 mb-0"
                                            id="benchUsersTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Skills</th>
                                                    <th>Name</th>
                                                    <th>Free Hours</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($benchUsersSummary as $index => $benchSkill)
                                                    @foreach ($benchSkill['users'] as $userIndex => $benchUser)
                                                        <tr class="skill-bench-row"
                                                            data-group="bench-{{ $benchSkill['id'] }}"
                                                            data-search="{{ strtolower($benchSkill['name'] . ' ' . $benchUser['name']) }}">
                                                            @if ($userIndex === 0)
                                                                <td data-group-cell="index"
                                                                    rowspan="{{ $benchSkill['users']->count() }}">
                                                                    {{ $index + 1 }}</td>
                                                                <td data-group-cell="skill"
                                                                    rowspan="{{ $benchSkill['users']->count() }}">
                                                                    {{ $benchSkill['name'] }}</td>
                                                            @endif
                                                            <td>{{ $benchUser['name'] }} - {{ $benchUser['employee_id'] }}
                                                            </td>
                                                            @if ($benchUser['free_hours'] < 150)
                                                                <td class="text-warning">
                                                                    {{ number_format($benchUser['free_hours'], 2) }}</td>
                                                            @else
                                                                <td class="color-bench">
                                                                    {{ number_format($benchUser['free_hours'], 2) }}</td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">No bench users
                                                            found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="text-muted f-12 mb-0 mt-2">Bench shows internal users with 20 or more free
                                        hours in {{ $now->format('F Y') }}.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row align-items-start mb-4">
            @if (!Auth::user()->hasRole('Admin'))
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Recent Tasks</h5>
                            <ul class="list-group flex-grow-1">
                                @foreach ($recentTasks as $task)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $task->title }}
                                        <span
                                            class="badge bg-primary rounded-pill">{{ $task->status == 'to_do' ? 'To Do' : 'In Progress' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('skillBenchSearch');

            if (!searchInput) {
                return;
            }

            const tables = ['skillsSetTable', 'benchUsersTable']
                .map(function(id) {
                    return document.getElementById(id);
                })
                .filter(Boolean);

            const originalTableBodies = new Map();

            tables.forEach(function(table) {
                const tbody = table.querySelector('tbody');
                if (tbody) {
                    originalTableBodies.set(table.id, tbody.innerHTML);
                }
            });

            function filterGroupedTable(table, searchTerm) {
                const tbody = table.querySelector('tbody');

                if (!tbody || !originalTableBodies.has(table.id)) {
                    return;
                }

                tbody.innerHTML = originalTableBodies.get(table.id);

                if (searchTerm === '') {
                    return;
                }

                const rows = Array.from(table.querySelectorAll('.skill-bench-row'));
                const groupedRows = rows.reduce(function(groups, row) {
                    const groupKey = row.getAttribute('data-group') || 'default';
                    groups[groupKey] = groups[groupKey] || [];
                    groups[groupKey].push(row);
                    return groups;
                }, {});

                Object.values(groupedRows).forEach(function(groupRows) {
                    const matchedRows = groupRows.filter(function(row) {
                        const haystack = row.getAttribute('data-search') || '';
                        return haystack.includes(searchTerm);
                    });

                    const indexCell = groupRows[0].querySelector('[data-group-cell="index"]');
                    const skillCell = groupRows[0].querySelector('[data-group-cell="skill"]');

                    groupRows.forEach(function(row) {
                        row.style.display = matchedRows.includes(row) ? '' : 'none';
                    });

                    if (indexCell) {
                        indexCell.style.display = matchedRows.length ? '' : 'none';
                    }

                    if (skillCell) {
                        skillCell.style.display = matchedRows.length ? '' : 'none';
                    }

                    if (!matchedRows.length) {
                        return;
                    }

                    const firstVisibleRow = matchedRows[0];

                    if (indexCell) {
                        if (!firstVisibleRow.contains(indexCell)) {
                            firstVisibleRow.insertBefore(indexCell, firstVisibleRow.firstChild);
                        }
                    }

                    if (skillCell) {
                        if (!firstVisibleRow.contains(skillCell)) {
                            const insertBeforeNode = firstVisibleRow.querySelector(
                                    'td:not([data-group-cell="index"])') ||
                                null;
                            firstVisibleRow.insertBefore(skillCell, insertBeforeNode);
                        }
                    }

                    if (indexCell) {
                        indexCell.rowSpan = matchedRows.length;
                    }

                    if (skillCell) {
                        skillCell.rowSpan = matchedRows.length;
                    }
                });
            }

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.trim().toLowerCase();

                tables.forEach(function(table) {
                    filterGroupedTable(table, searchTerm);
                });
            });
        });
    </script>
@endpush
