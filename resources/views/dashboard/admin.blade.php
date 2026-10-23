@extends('layouts.app')
@section('title')
    Dashboard
@endsection
@section('content')
    <div class="container-fluid">
        

            <div class="row">

                {{-- <div class="col-sm-6 col-md-2 mb-4">
                    <div class="card shadow-sm w-100 h-100">
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
                                                $textColor = $brightness > 159 ? '#111111' : '#ffffff';
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
                                        <tr>
                                            <td style="font-weight: 600;">
                                                Total Resources
                                            </td>
                                            <td class="text-center fw-semibold">
                                                <a href="{{ route('users.listing', ['tag_scope' => 'internal']) }}"
                                                    class="text-decoration-none">
                                                    {{ $totalResources }}
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-3 mb-4">
                    <div class="card shadow-sm w-100 h-100">
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
                                                    <a href="{{ route('users.index', ['work_type' => $label, 'month' => $now->month, 'year' => $now->year]) }}"
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
                <div class="col-md-3 mb-4">
                    <div class="card shadow-sm w-100 h-100">
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
                </div> --}}

            </div>
    </div>

<!--new design-->

<div class="dashboard_design">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="dashboard_content_header header-wrapper">
                    <div class="row header-row">
                        <div class="col-md-5">
                            <h2>Pro Taskr Tracker</h2>
                            <p>Track resources, skills, utilization and profitability in one place.</p>
                        </div>
                        <div class="col-md-7 dashboard-date-picker">
                            @php
                                $dashboardActiveDate = $now;
                                $dashboardMonthOptions = [];
                                for ($offset = -6; $offset <= 6; $offset++) {
                                    $dashboardMonthOptions[] = now()->startOfMonth()->addMonths($offset);
                                }
                                if (!collect($dashboardMonthOptions)->contains(fn ($option) => $option->isSameMonth($dashboardActiveDate))) {
                                    $dashboardMonthOptions[] = $dashboardActiveDate->copy()->startOfMonth();
                                    usort($dashboardMonthOptions, fn ($a, $b) => $a->timestamp <=> $b->timestamp);
                                }
                            @endphp
                            <div class="dropdown dashboard-date-dropdown">
                                <button class="dashboard-date-picker-button dropdown-toggle" type="button" id="dashboardDatePickerBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-calendar-date"></i>
                                    <span>{{ $dashboardActiveDate->format('F Y') }}</span>
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow header-date-menu" aria-labelledby="dashboardDatePickerBtn">
                                    <li class="dropdown-header text-uppercase fw-bold text-muted small px-3 py-2">Select Month &amp; Year</li>
                                    @foreach ($dashboardMonthOptions as $optionDate)
                                        @php
                                            $isSelected = $optionDate->isSameMonth($dashboardActiveDate);
                                            $isRealCurrent = $optionDate->isSameMonth(now());
                                        @endphp
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center justify-content-between px-3 py-2 {{ $isSelected ? 'active fw-bold' : '' }}" href="{{ route('dashboard', ['month' => $optionDate->month, 'year' => $optionDate->year]) }}">
                                                <span><i class="bi bi-calendar3 me-2 {{ $isSelected ? 'text-white' : 'text-primary' }}"></i>{{ $optionDate->format('F Y') }}</span>
                                                @if ($isSelected)
                                                    <i class="bi bi-check-lg ms-2 text-white"></i>
                                                @elseif ($isRealCurrent)
                                                    <span class="badge bg-secondary-subtle text-secondary ms-2" style="font-size: 10px;">Current</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="row header_count_row">
                        <div class="col-count-card col-md-3">
                            <a href="{{ route('users.index') }}" class="report-box resources" style="text-decoration:none;color:inherit">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                                    </svg>
                                </div>
                                <div class="report_content">
                                    <span class="report_title">Total Resources</span>
                                    <h4>{{ $totalResources }}</h4>
                                    <span class="rating rating-up"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-short" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 12a.5.5 0 0 0 .5-.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 .5.5"/></svg>
                                    10% </span>
                                    <span class="report_time">vs last month</span>
                                </div>
                                <div class="graph_svg">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100%" height="100%">
                                        <defs>
                                            <!-- Solid/Subtle blue gradient matching UI -->
                                            <linearGradient id="barFill" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="#6ea8fe" />
                                            <stop offset="100%" stop-color="#4d92fa" />
                                            </linearGradient>

                                            <!-- Outer soft white glow -->
                                            <filter id="softGlow" x="-20%" y="-20%" width="140%" height="140%">
                                            <feMorphology in="SourceGraphic" operator="dilate" radius="2" result="dilated" />
                                            <feGaussianBlur in="dilated" stdDeviation="3" result="blurred" />
                                            <feFlood flood-color="#ffffff" flood-opacity="0.95" result="glowColor" />
                                            <feComposite in="glowColor" in2="blurred" operator="in" result="whiteGlow" />
                                            <feMerge>
                                                <feMergeNode in="whiteGlow" />
                                                <feMergeNode in="SourceGraphic" />
                                            </feMerge>
                                            </filter>
                                        </defs>

                                        <g filter="url(#softGlow)" fill="url(#barFill)">
                                            <!-- Bar 1: Rounded top (r=7), flat bottom at y=88 -->
                                            <path d="M 8 88 L 8 70 A 7 7 0 0 1 22 70 L 22 88 Z" />

                                            <!-- Bar 2: Rounded top (r=7), flat bottom at y=88 -->
                                            <path d="M 30 88 L 30 56 A 7 7 0 0 1 44 56 L 44 88 Z" />

                                            <!-- Bar 3: Rounded top (r=7), flat bottom at y=88 -->
                                            <path d="M 52 88 L 52 44 A 7 7 0 0 1 66 44 L 66 88 Z" />

                                            <!-- Bar 4: Rounded top (r=7), flat bottom at y=88 -->
                                            <path d="M 74 88 L 74 26 A 7 7 0 0 1 88 26 L 88 88 Z" />
                                        </g>
                                        </svg>
                                </div>
                            </a>
                        </div>
                        <div class="col-count-card col-md-3">
                            <a href="{{ route('clients.index') }}" class="report-box clients" style="text-decoration:none;color:inherit">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-buildings" viewBox="0 0 16 16">
                                        <path d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V4.5a.5.5 0 0 1 .276-.447l8-4a.5.5 0 0 1 .487.022M6 8.694 1 10.36V15h5zM7 15h2v-1.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5V15h2V1.309l-7 3.5z"/>
                                        <path d="M2 11h1v1H2zm2 0h1v1H4zm-2 2h1v1H2zm2 0h1v1H4zm4-4h1v1H8zm2 0h1v1h-1zm-2 2h1v1H8zm2 0h1v1h-1zm2-2h1v1h-1zm0 2h1v1h-1zM8 7h1v1H8zm2 0h1v1h-1zm2 0h1v1h-1zM8 5h1v1H8zm2 0h1v1h-1zm2 0h1v1h-1zM0-2h1v1h-1z"/>
                                    </svg>
                                </div>
                                <div class="report_content">
                                    <span class="report_title">Total Clients</span>
                                    <h4>{{ $currentClients }}</h4>
                                    <span class="rating rating-down"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-down-short" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 4a.5.5 0 0 1 .5.5v5.793l2.146-2.147a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 1 1 .708-.708L7.5 10.293V4.5A.5.5 0 0 1 8 4"/>
                                    </svg>
                                    20% </span>
                                    <span class="report_time">vs last month</span>
                                </div>
                                <div class="graph_svg">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="57" height="57">
                                        <!-- Bar 1 -->
                                        <path d="M 2.5 22 V 16.75 A 1.75 1.75 0 0 1 6 16.75 V 22 Z" fill="#9d66fd" />
                                        
                                        <!-- Bar 2 -->
                                        <path d="M 8 22 V 12.75 A 1.75 1.75 0 0 1 11.5 12.75 V 22 Z" fill="#9d66fd" />
                                        
                                        <!-- Bar 3 -->
                                        <path d="M 13.5 22 V 8.75 A 1.75 1.75 0 0 1 17 8.75 V 22 Z" fill="#9d66fd" />
                                        
                                        <!-- Bar 4 -->
                                        <path d="M 19 22 V 3.75 A 1.75 1.75 0 0 1 22.5 3.75 V 22 Z" fill="#9d66fd" />
                                    </svg>
                                </div>
                            </a>
                        </div>
                        <div class="col-count-card col-md-3">
                            <a href="{{ route('services.listing.index', ['month' => $now->format('Y-m')]) }}" class="report-box total-hours" style="text-decoration:none;color:inherit">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16">
                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>
                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/>
                                    </svg>
                                </div>
                                <div class="report_content">
                                    <span class="report_title">Total Hours</span>
                                    <h4>{{ $totalHours }} Hrs</h4>
                                    <span class="rating rating-up"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-short" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 12a.5.5 0 0 0 .5-.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 .5.5"/></svg>
                                    10% </span>
                                    <span class="report_time">vs last month</span>
                                </div>
                                <div class="graph_svg">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 110" width="100%" height="100%">
                                        <defs>
                                            <!-- Turquoise-to-emerald gradient -->
                                            <linearGradient id="barGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#00e5a3" />
                                            <stop offset="100%" stop-color="#02ce85" />
                                            </linearGradient>

                                            <!-- Soft outer glow -->
                                            <filter id="softGlow" x="-20%" y="-20%" width="140%" height="140%">
                                            <feGaussianBlur stdDeviation="1.5" result="blur" />
                                            <feMerge>
                                                <feMergeNode in="blur" />
                                                <feMergeNode in="SourceGraphic" />
                                            </feMerge>
                                            </filter>
                                        </defs>

                                        <g filter="url(#softGlow)" fill="url(#barGrad)">
                                            <!-- Bar 1 (Height: 38, Top rounded only) -->
                                            <path d="M 4,104 L 4,74 A 8,8 0 0 1 20,74 L 20,104 Z" />

                                            <!-- Bar 2 (Height: 56, Top rounded only) -->
                                            <path d="M 28,104 L 28,56 A 8,8 0 0 1 44,56 L 44,104 Z" />

                                            <!-- Bar 3 (Height: 74, Top rounded only) -->
                                            <path d="M 52,104 L 52,38 A 8,8 0 0 1 68,38 L 68,104 Z" />

                                            <!-- Bar 4 (Height: 96, Top rounded only) -->
                                            <path d="M 76,104 L 76,16 A 8,8 0 0 1 92,16 L 92,104 Z" />
                                        </g>
                                    </svg>
                                </div>
                            </a>
                        </div>
                        <div class="col-count-card col-md-3">
                            <a href="{{ route('services.listing.index', ['month' => $now->format('Y-m')]) }}" class="report-box assigned-hours" style="text-decoration:none;color:inherit">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-briefcase-fill" viewBox="0 0 16 16">
                                        <path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v1.384l7.614 2.03a1.5 1.5 0 0 0 .772 0L16 5.884V4.5A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5"/>
                                        <path d="M0 12.5A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5V6.85L8.129 8.947a.5.5 0 0 1-.258 0L0 6.85z"/>
                                    </svg>
                                </div>
                                <div class="report_content">
                                    <span class="report_title">Assigned Hours</span>
                                    <h4>{{ $assignedHours }} Hrs</h4>
                                    <span class="rating rating-up"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-short" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 12a.5.5 0 0 0 .5-.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 .5.5"/></svg>
                                    15% </span>
                                    <span class="report_time">vs last month</span>
                                </div>
                                <div class="graph_svg">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 110" width="100%" height="100%">
                                        <defs>
                                            <linearGradient id="assignedBarGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#818cf8" />
                                                <stop offset="100%" stop-color="#4f46e5" />
                                            </linearGradient>
                                            <filter id="softGlowAssigned" x="-20%" y="-20%" width="140%" height="140%">
                                                <feGaussianBlur stdDeviation="1.5" result="blur" />
                                                <feMerge>
                                                    <feMergeNode in="blur" />
                                                    <feMergeNode in="SourceGraphic" />
                                                </feMerge>
                                            </filter>
                                        </defs>
                                        <g filter="url(#softGlowAssigned)" fill="url(#assignedBarGrad)">
                                            <path d="M 4,104 L 4,60 A 8,8 0 0 1 20,60 L 20,104 Z" />
                                            <path d="M 28,104 L 28,42 A 8,8 0 0 1 44,42 L 44,104 Z" />
                                            <path d="M 52,104 L 52,70 A 8,8 0 0 1 68,70 L 68,104 Z" />
                                            <path d="M 76,104 L 76,24 A 8,8 0 0 1 92,24 L 92,104 Z" />
                                        </g>
                                    </svg>
                                </div>
                            </a>
                        </div>
                        <div class="col-count-card col-md-3">
                            <a href="{{ route('services.listing.index', ['month' => $now->format('Y-m')]) }}" class="report-box free-hours" style="text-decoration:none;color:inherit">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lightning-charge-fill" viewBox="0 0 16 16">
                                        <path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z"/>
                                    </svg>
                                </div>
                                <div class="report_content">
                                    <span class="report_title">Free Hours</span>
                                    <h4>{{ $totalFreeHours }} Hrs</h4>
                                    <span class="rating rating-down"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-down-short" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 4a.5.5 0 0 1 .5.5v5.793l2.146-2.147a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 1 1 .708-.708L7.5 10.293V4.5A.5.5 0 0 1 8 4"/>
                                    </svg>
                                    20% </span>
                                    <span class="report_time">vs last month</span>
                                </div>
                                <div class="graph_svg">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 110" width="100%" height="100%">
                                        <defs>
                                            <!-- Orange gradient -->
                                            <linearGradient id="orangeGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#ff9d52" />
                                            <stop offset="100%" stop-color="#ff6a1a" />
                                            </linearGradient>

                                            <!-- Soft outer glow/blur -->
                                            <filter id="softGlow" x="-20%" y="-20%" width="140%" height="140%">
                                            <feGaussianBlur stdDeviation="1.5" result="blur" />
                                            <feMerge>
                                                <feMergeNode in="blur" />
                                                <feMergeNode in="SourceGraphic" />
                                            </feMerge>
                                            </filter>
                                        </defs>

                                        <g filter="url(#softGlow)" fill="url(#orangeGrad)">
                                            <!-- Bar 1 (Height: 38, flat bottom, rounded top) -->
                                            <path d="M 4,104 L 4,74 A 8,8 0 0 1 20,74 L 20,104 Z" />

                                            <!-- Bar 2 (Height: 56, flat bottom, rounded top) -->
                                            <path d="M 28,104 L 28,56 A 8,8 0 0 1 44,56 L 44,104 Z" />

                                            <!-- Bar 3 (Height: 74, flat bottom, rounded top) -->
                                            <path d="M 52,104 L 52,38 A 8,8 0 0 1 68,38 L 68,104 Z" />

                                            <!-- Bar 4 (Height: 96, flat bottom, rounded top) -->
                                            <path d="M 76,104 L 76,16 A 8,8 0 0 1 92,16 L 92,104 Z" />
                                        </g>
                                    </svg>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                            <div class="row chart_box_row">

                                <div class="col-sm-6 col-md-4">
                                    <div class="card shadow-sm w-100 h-100">
                                        <div class="card-body">
                                            <div class="summery_header">
                                                <h5><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tags-fill" viewBox="0 0 16 16">
                                                    <path d="M2 2a1 1 0 0 1 1-1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 2 6.586zm3.5 4a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                                                    <path d="M1.293 7.793A1 1 0 0 1 1 7.086V2a1 1 0 0 0-1 1v4.586a1 1 0 0 0 .293.707l7 7a1 1 0 0 0 1.414 0l.043-.043z"/>
                                                </svg>Tags Summary</h5>
                                                
                                            </div>
                                            

                                            @php
                                                $tagChartCircumference = 389.56;
                                                $tagChartTotal = $tagSummary->sum('users_count');
                                                $tagChartOffset = 0;
                                            @endphp
                                            <div class="summery-card">
                                                <!-- Donut Chart -->
                                                <div class="chart-container">
                                                <svg viewBox="0 0 160 160">
                                                    @if ($tagChartTotal > 0)
                                                        @foreach ($tagSummary as $tag)
                                                            @php
                                                                $tagSegment = $tagChartCircumference * $tag->users_count / $tagChartTotal;
                                                                $tagColor = $tag->color ?: '#6c757d';
                                                            @endphp
                                                            <circle cx="80" cy="80" r="62"
                                                                style="fill: none; stroke: {{ $tagColor }};"
                                                                stroke-dasharray="{{ $tagSegment }} {{ $tagChartCircumference - $tagSegment }}"
                                                                stroke-dashoffset="-{{ $tagChartOffset }}" />
                                                            @php $tagChartOffset += $tagSegment; @endphp
                                                        @endforeach
                                                    @endif
                                                </svg>

                                                <!-- Center Text -->
                                                <div class="center-text">
                                                    <div class="total">{{ $totalResources }}</div>
                                                    <div class="label">Total<br>Resources</div>
                                                </div>
                                                </div>

                                                <!-- Details List -->
                                                <div class="list">
                                                    <table class="graph_table">
                                                        @forelse ($tagSummary as $tag)
                                                            @php
                                                                $tagColor = $tag->color ?: '#6c757d';
                                                                $tagPercentage = $tagChartTotal > 0 ? ($tag->users_count / $tagChartTotal) * 100 : 0;
                                                            @endphp
                                                            <tr>
                                                                <td><span class="dot" style="background-color: {{ $tagColor }};"></span><span class="name">{{ strtoupper($tag->name) }}</span></td>
                                                                <td><span class="count">
                                                                        <a href="{{ route('users.listing', ['tag_id' => $tag->id, 'tag_scope' => 'internal']) }}" class="text-decoration-none">
                                                                            {{ $tag->users_count }}
                                                                        </a>
                                                                    </span>
                                                                </td>
                                                                <td><span class="percentage">{{ number_format($tagPercentage, 0) }}%</span></td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-center text-muted">No tags found</td>
                                                            </tr>
                                                        @endforelse
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-md-4">
                                    <div class="card shadow-sm w-100 h-100">
                                        <div class="card-body">
                                            <div class="summery_header">
                                                <h5><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-buildings" viewBox="0 0 16 16">
                                                        <path d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V4.5a.5.5 0 0 1 .276-.447l8-4a.5.5 0 0 1 .487.022M6 8.694 1 10.36V15h5zM7 15h2v-1.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5V15h2V1.309l-7 3.5z"/>
                                                        <path d="M2 11h1v1H2zm2 0h1v1H4zm-2 2h1v1H2zm2 0h1v1H4zm4-4h1v1H8zm2 0h1v1h-1zm-2 2h1v1H8zm2 0h1v1h-1zm2-2h1v1h-1zm0 2h1v1h-1zM8 7h1v1H8zm2 0h1v1h-1zm2 0h1v1h-1zM8 5h1v1H8zm2 0h1v1h-1zm2 0h1v1h-1zm0-2h1v1h-1z"/>
                                                    </svg>Work Type Distribution</h5>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="work-type-table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width:25%">Name</th>
                                                            <th style="width:30%" class="no_users">Number of Users</th>
                                                            <th style="width:45%">Percentage</th>
                                                        </tr>
                                                    </thead>
                                                    @php
                                                        $workingHoursTotal = collect($workingHoursSummary)->sum();
                                                    @endphp
                                                    <tbody>
                                                        @forelse ($workingHoursSummary as $label => $count)
                                                            @php
                                                                $workTypeClass = strtolower(str_replace([' +', ' '], ['_plus', '_'], trim($label)));
                                                                $workTypePercentage = $workingHoursTotal > 0 ? ($count / $workingHoursTotal) * 100 : 0;
                                                                $workTypeBarClass = str_replace('_', '', $workTypeClass) . '_process_bar';
                                                            @endphp
                                                            <tr>
                                                                <td class="{{ $workTypeClass }}">{{ $label }}</td>
                                                                <td>
                                                                    <a href="{{ route('users.index', ['work_type' => $label, 'month' => $now->month, 'year' => $now->year]) }}">{{ $count }}</a>
                                                                </td>
                                                                <td class="d-flex align-items-center justify-content-end gap-2">
                                                                    <progress id="file" class="{{ $workTypeBarClass }}" value="{{ $workTypePercentage }}" max="100">{{ number_format($workTypePercentage, 0) }}%</progress><span>{{ number_format($workTypePercentage, 0) }}%</span>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-center text-muted">No work types found</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card shadow-sm w-100 h-100">
                                        <div class="card-body recent_history">
                                            <div class="summery_header">
                                                <h5><i class="bi bi-clock-history" aria-hidden="true"></i> Recent History</h5>
                                            </div>
                                            @if ($recentActivity)
                                                <div class="recent-history-timeline">
                                                    <div class="recent-history-item {{ $recentActivity['type'] }}">
                                                        <span class="recent-history-marker" aria-hidden="true">
                                                            <i class="bi {{ $recentActivity['type'] === 'client' ? 'bi-person-plus' : 'bi-briefcase' }}"></i>
                                                        </span>
                                                        <div class="recent-history-content">
                                                            <div class="recent-history-title">{{ $recentActivity['title'] }}</div>
                                                            <div class="recent-history-name">{{ $recentActivity['name'] }}</div>
                                                            @if ($recentActivity['type'] === 'service' && $recentActivity['client'])
                                                                <div class="recent-history-client">For {{ $recentActivity['client'] }}</div>
                                                            @endif
                                                            <time class="recent-history-date" datetime="{{ $recentActivity['created_at']->toIso8601String() }}">
                                                                {{ $recentActivity['created_at']->format('d M Y, h:i A') }}
                                                            </time>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="recent-history-empty">No clients or services have been added yet.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-12 mt-3">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="list_employee">
                                                <div class="left_header">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                                                         <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                                                    </svg>
                                                    <span>
                                                        <h4>Bench</h4>
                                                        <p>Internal users with 20 or more free hours in {{ $now->format('F Y') }}.</p>
                                                    </span>
                                                </div>
                                                <div class="right_header">
                                                    <div class="search_box">
                                                        <button><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"></path>
                                                            </svg>
                                                        </button>
                                                        <input type="text" name="" placeholder="Search Skills and Users...">
                                                    </div>
                                                    <button class="filter_btn"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.3.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M96 128C78.3 128 64 142.3 64 160C64 177.7 78.3 192 96 192L182.7 192C195 220.3 223.2 240 256 240C288.8 240 317 220.3 329.3 192L544 192C561.7 192 576 177.7 576 160C576 142.3 561.7 128 544 128L329.3 128C317 99.7 288.8 80 256 80C223.2 80 195 99.7 182.7 128L96 128zM96 288C78.3 288 64 302.3 64 320C64 337.7 78.3 352 96 352L342.7 352C355 380.3 383.2 400 416 400C448.8 400 477 380.3 489.3 352L544 352C561.7 352 576 337.7 576 320C576 302.3 561.7 288 544 288L489.3 288C477 259.7 448.8 240 416 240C383.2 240 355 259.7 342.7 288L96 288zM96 448C78.3 448 64 462.3 64 480C64 497.7 78.3 512 96 512L150.7 512C163 540.3 191.2 560 224 560C256.8 560 285 540.3 297.3 512L544 512C561.7 512 576 497.7 576 480C576 462.3 561.7 448 544 448L297.3 448C285 419.7 256.8 400 224 400C191.2 400 163 419.7 150.7 448L96 448z"/></svg></button>
                                                    <button class="export_btn"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                                                        </svg>Export
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="tab-content list_employee_table" id="skillBenchTabsContent">
                                                {{-- <div class="tab-pane fade" id="skills-set-pane" role="tabpanel"
                                                    aria-labelledby="skills-set-tab" tabindex="0">
                                                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                                        <table class="table table-bordered table-sm align-middle f-14 mb-0"
                                                            id="skillsSetTable">
                                                            <thead>
                                                                <tr>
                                                                    <th class="number_heading">#</th>
                                                                    <th class="skill_heading">Skills</th>
                                                                    <th class="name_heading">Name</th>
                                                                    <th class="hours_heading">Free Hours</th>
                                                                    <th class="available_heading">Available</th>
                                                                    <th class="status_heading">Status</th>
                                                                    <th class="action action_heading">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>1</td>
                                                                    <td class="emp_skils"><span>Node</span><span>Mango</span><span>Node.js</span></td>
                                                                    <td>Riken Panchal</td>
                                                                    <td>120 hrs</td>
                                                                    <td><span class="available"><i class="bi bi-dot"></i>Available</span></td>
                                                                    <td class="emp_status"><span class="st_bench">Bench</span>
                                                                    <!-- <span class="st_full_plus">Full Plus</span><span class="st_full">Full</span><span class="st_half_plus">Half Plus</span><span class="st_half">Half</span><span class="st_adhoc">ADHOC</span> -->
                                                                </td>
                                                                    <td class="action">
                                                                        <button class="emp_status_action">
                                                                            <i class="bi bi-three-dots"></i>
                                                                            <ul>
                                                                                <li><a href="#">Edit</a></li>
                                                                                <li><a href="#">Delete</a></li>
                                                                            </ul>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                                @forelse($skillSetSummary as $index => $skill)
                                                                    
                                                                    <tr class="skill-bench-row"
                                                                        data-skill="{{ strtolower($skill['name']) }}"
                                                                        data-users='@json($skill['users'])'>

                                                                        <td>{{ $index + 1 }}</td>

                                                                        <td>{{ $skill['name'] }}</td>

                                                                        <td class="users-cell">
                                                                            {{ collect($skill['users'])->map(fn($u) => $u->name . ' - ' . $u->employee_id)->join(', ') }}
                                                                        </td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td>

                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="7" class="text-center text-muted empty_table">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                                                                                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                                                                            </svg>
                                                                        <p><strong>No bench users found</strong>Bench shows internal users with 20 or more free hours in September 2026.   </p>
                                                                        </td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div> --}}

                                                <div class="tab-pane fade show active" id="bench-users-pane" role="tabpanel"
                                                    aria-labelledby="bench-users-tab" tabindex="0">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm align-middle f-14 mb-0"
                                                            id="benchUsersTable">
                                                            <thead>
                                                                <tr>
                                                                    <th class="number_heading">#</th>
                                                                    <th class="skill_heading">Skills</th>
                                                                    <th class="name_heading">Name</th>
                                                                    <th class="hours_heading">Free Hours</th>
                                                                    <th class="available_heading">Available</th>
                                                                    <th class="status_heading">Status</th>
                                                                    <th class="action action_heading">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php
                                                                    $benchTableUsers = collect();
                                                                    foreach ($benchUsersSummary as $benchSkill) {
                                                                        foreach ($benchSkill['users'] as $benchUser) {
                                                                            $userKey = $benchUser['employee_id'] ?? $benchUser['id'] ?? $benchUser['name'];
                                                                            if (!$benchTableUsers->has($userKey)) {
                                                                                $benchTableUsers->put($userKey, [
                                                                                    'user' => $benchUser,
                                                                                    'skills' => collect(),
                                                                                ]);
                                                                            }
                                                                            $benchTableUsers->get($userKey)['skills']->push($benchSkill['name']);
                                                                        }
                                                                    }
                                                                @endphp
                                                                @forelse($benchTableUsers as $index => $benchRow)
                                                                    @php($benchUser = $benchRow['user'])
                                                                    <tr class="skill-bench-row"
                                                                        data-search="{{ strtolower(collect($benchRow['skills'])->join(' ') . ' ' . $benchUser['name']) }}">
                                                                        <td>{{ $loop->iteration }}</td>
                                                                        <td class="emp_skils">
                                                                            @foreach ($benchRow['skills']->unique() as $skillName)
                                                                                <span>{{ $skillName }}</span>
                                                                            @endforeach
                                                                        </td>
                                                                        <td>{{ $benchUser['name'] }} - {{ $benchUser['employee_id'] }}</td>
                                                                        @if ($benchUser['free_hours'] < 150)
                                                                            <td class="text-warning">{{ number_format($benchUser['free_hours'], 2) }} hrs</td>
                                                                        @else
                                                                            <td class="color-bench">{{ number_format($benchUser['free_hours'], 2) }} hrs</td>
                                                                        @endif
                                                                        <td><span class="available"><i class="bi bi-dot"></i>Available</span></td>
                                                                        <td class="emp_status"><span class="st_bench">Bench</span></td>
                                                                        <td class="action">
                                                                            <button class="emp_status_action">
                                                                                <i class="bi bi-three-dots"></i>
                                                                                <ul>
                                                                                    <li><a href="#">Edit</a></li>
                                                                                    <li><a href="#">Delete</a></li>
                                                                                </ul>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="7" class="text-center text-muted empty_table">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                                                                                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                                                                            </svg>
                                                                        <p><strong>No bench users found</strong>Bench shows internal users with 20 or more free hours in {{ $now->format('F Y') }}.</p>
                                                                        </td>
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





                        <div class="row align-items-start mt-4">
                        </div>
            </div>
        </div>
    </div>
</div>
<!--End new design-->
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

            function filterSkillsTable(table, searchTerm) {
                const rows = table.querySelectorAll('.skill-bench-row');

                rows.forEach(function(row) {
                    const skillName = row.getAttribute('data-skill') || '';
                    const users = JSON.parse(row.getAttribute('data-users') || '[]');

                    let matchedUsers = users.filter(u => {
                        const text = (u.name + ' ' + u.employee_id).toLowerCase();
                        return text.includes(searchTerm);
                    });

                    // Also allow skill name search
                    const skillMatch = skillName.includes(searchTerm);

                    if (searchTerm === '') {
                        matchedUsers = users;
                    }

                    if (matchedUsers.length || skillMatch) {
                        row.style.display = '';

                        const cell = row.querySelector('.users-cell');

                        const displayUsers = (matchedUsers.length || !skillMatch) ?
                            matchedUsers :
                            users;

                        cell.innerHTML = displayUsers
                            .map(u => `${u.name} - ${u.employee_id}`)
                            .join(', ');
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

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

                // tables.forEach(function(table) {
                //     filterGroupedTable(table, searchTerm);
                // });
                tables.forEach(function(table) {
                    if (table.id === 'skillsSetTable') {
                        filterSkillsTable(table, searchTerm);
                    } else {
                        filterGroupedTable(table, searchTerm); // bench stays same
                    }
                });
            });
        });
    </script>
@endpush
