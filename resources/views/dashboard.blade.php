@extends('layouts.app')
@section('title')
    Dashboard
@endsection
@section('content')
    <div class="container">
        <h2>VE Emp Tracker</h2>

        <div class="row mb-4 align-items-start">
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
            <div class="col-md-4 col-lg-2 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title f-14 mb-1">Tags Summary</h5>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0 align-middle f-12">
                                <tbody>
                                    @forelse($tagSummary as $index => $tag)
                                        @php
                                            $tagColor = $tag->color ?: '#6c757d';
                                            $hex = ltrim($tagColor, '#');
                                            if (strlen($hex) === 3) {
                                                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                                            }
                                            $r = hexdec(substr($hex, 0, 2));
                                            $g = hexdec(substr($hex, 2, 2));
                                            $b = hexdec(substr($hex, 4, 2));
                                            $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                                            $textColor = $brightness > 140 ? '#111111' : '#ffffff';
                                        @endphp
                                        <tr>
                                            <td style="font-weight: 600; background-color: {{ $tagColor }}; color: {{ $textColor }};">
                                                {{ strtoupper($tag->name) }}
                                            </td>
                                            <td class="text-center fw-semibold">
                                                <a href="{{ route('users.listing', ['tag_id' => $tag->id, 'tag_scope' => 'internal']) }}" class="text-decoration-none">
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

            <div class="col-md-8 col-lg-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title f-14 mb-1">Work Type</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0 align-middle f-12 text-center">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        @foreach($workingHoursSummary as $label => $count)
                                            <th>{{ $label }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>Number of Users</th>
                                        @foreach($workingHoursSummary as $label => $count)
                                            <td class="fw-semibold text-primary">
                                                <a href="{{ route('users.listing', ['work_type' => $label]) }}" class="text-decoration-none">
                                                    {{ $count }}
                                                </a>
                                            </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mb-0 f-12 mt-2">
                            {{ $now->format('F Y') }}@if($masterHours), master hours: {{ $masterHours }}@endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title f-14 mb-1">Profit / Loss Summary</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0 align-middle text-center f-12">
                                <tbody>
                                    <tr>
                                        <td class="p-1">
                                            <div class="fs-6 fw-bold">{{ number_format($profitLossSummary['total_hours'], 0) }}</div>
                                            <div class="text-muted small f-10">Total Hours</div>
                                            <div class="small f-10">{{ $profitLossSummary['internal_users_count'] }} Internal Users</div>
                                        </td>
                                        <td class="p-1">
                                            <div class="fs-6 fw-bold">{{ number_format($profitLossSummary['assigned_hours'], 0) }}</div>
                                            <div class="text-muted small f-10">Assigned Hours</div>
                                            <div class="small f-10">Current Month Services</div>
                                        </td>
                                        @if ($profitLossSummary['loss_percentage'] != 0)
                                            <td class="p-1">
                                                <div class="fs-6 fw-bold text-danger">
                                                    <i class="bi bi-graph-down-arrow me-1"></i>{{ $profitLossSummary['loss_percentage'] }}%
                                                </div>
                                                <div class="text-muted small f-10">Loss</div>
                                                <div class="small text-danger f-10">{{ number_format($profitLossSummary['loss_hours'], 0) }} hrs</div>
                                            </td>
                                        @else
                                            <td class="p-1">
                                                <div class="fs-6 fw-bold text-success">
                                                    <i class="bi bi-graph-up-arrow me-1"></i>{{ $profitLossSummary['profit_percentage'] }}%
                                                </div>
                                                <div class="text-muted small f-10">Profit</div>
                                                <div class="small text-success f-10">{{ number_format($profitLossSummary['profit_hours'], 0) }} hrs</div>
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
        
        <div class="row mb-4">
            @if (!Auth::user()->hasRole('Admin'))
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
            @endif
        </div>
    </div>
@endsection
