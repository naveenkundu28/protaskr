@extends('layouts.app')
@extends('layouts.header')
@section('title')
    Dashboard
@endsection
@section('content')
    <div class="container mt-80">
        <h2>VE Emp Tracker</h2>

        <div class="row mb-4">
            
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Tasks</h5>
                        <p class="card-text flex-grow-1">You have <strong>{{ $tasksCount }}</strong> tasks pending.</p>
                        <a href="{{ route('clients.index') }}" class="btn btn-primary mt-auto">View Tasks</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row align-items-start mb-4">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Recent Tasks</h5>
                        <ul class="list-group flex-grow-1">
                            @foreach($recentTasks as $task)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $task->title }}
                                    <span class="badge bg-primary rounded-pill">{{ $task->status == 'to_do' ? 'To Do' : 'In Progress' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('skillBenchSearch');

        if (!searchInput) {
            return;
        }

        const tables = ['skillsSetTable', 'benchUsersTable']
            .map(function (id) {
                return document.getElementById(id);
            })
            .filter(Boolean);

        const originalTableBodies = new Map();

        tables.forEach(function (table) {
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
            const groupedRows = rows.reduce(function (groups, row) {
                const groupKey = row.getAttribute('data-group') || 'default';
                groups[groupKey] = groups[groupKey] || [];
                groups[groupKey].push(row);
                return groups;
            }, {});

            Object.values(groupedRows).forEach(function (groupRows) {
                const matchedRows = groupRows.filter(function (row) {
                    const haystack = row.getAttribute('data-search') || '';
                    return haystack.includes(searchTerm);
                });

                const indexCell = groupRows[0].querySelector('[data-group-cell="index"]');
                const skillCell = groupRows[0].querySelector('[data-group-cell="skill"]');

                groupRows.forEach(function (row) {
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
                        const insertBeforeNode = firstVisibleRow.querySelector('td:not([data-group-cell="index"])')
                            || null;
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

        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.trim().toLowerCase();

            tables.forEach(function (table) {
                filterGroupedTable(table, searchTerm);
            });
        });
    });
</script>
@endpush
