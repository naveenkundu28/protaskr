@extends('layouts.app')
@extends('layouts.header')
@section('title')
    Dashboard
@endsection
@section('content')
    <div class="container-fluid">
        
        @session('success')
            <div class="alert alert-success" role="alert">
                {{ $value }}
            </div>
        @endsession

        @if (Auth::user()->hasRole('Admin'))
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
        @endif
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
                        <div class="col-md-7">
                            <div class="icon_with_content">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="100%" height="100%" fill="none">
                                    <defs>
                                        <!-- Bar 1 (Shortest - Sky/Blue) -->
                                        <linearGradient id="bar1Grad" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" stop-color="#4B8CFF"/>
                                        <stop offset="100%" stop-color="#2563EB"/>
                                        </linearGradient>

                                        <!-- Bar 2 (Middle - Vibrant Blue) -->
                                        <linearGradient id="bar2Grad" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" stop-color="#3B82F6"/>
                                        <stop offset="100%" stop-color="#1D4ED8"/>
                                        </linearGradient>

                                        <!-- Bar 3 (Tallest - Blue to Deep Violet/Purple) -->
                                        <linearGradient id="bar3Grad" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" stop-color="#4F46E5"/>
                                        <stop offset="50%" stop-color="#6366F1"/>
                                        <stop offset="100%" stop-color="#7C3AED"/>
                                        </linearGradient>

                                        <!-- Trending Growth Line / Rocket Arrow -->
                                        <linearGradient id="lineGrad" x1="0%" y1="100%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#2563EB"/>
                                        <stop offset="50%" stop-color="#4F46E5"/>
                                        <stop offset="100%" stop-color="#7C3AED"/>
                                        </linearGradient>
                                    </defs>
                                    <rect x="15" y="38" width="8" height="14" rx="3.5" fill="url(#bar1Grad)"/>
                                    <rect x="28" y="27" width="8.5" height="25" rx="4" fill="url(#bar2Grad)"/>
                                    <rect x="42" y="16" width="9" height="36" rx="4.5" fill="url(#bar3Grad)"/>
                                    <path d="M12 52C12 50.8954 12.8954 50 14 50H51C52.1046 50 53 50.8954 53 52C53 53.1046 52.1046 54 51 54H14C12.8954 54 12 53.1046 12 52Z" fill="#3B82F6" fill-opacity="0.35"/>
                                    <path d="M17 41.5 C21 40, 24 35, 28 32 C31.5 29.5, 33 21, 37 17.5 C40 15, 43 14, 46.5 10.5" 
                                            stroke="url(#lineGrad)" 
                                            stroke-width="3" 
                                            stroke-linecap="round" 
                                            stroke-linejoin="round"/>
                                    <path d="M43.5 9L49 8L48 13.5Z" fill="#6366F1"/>
                                    <circle cx="16.5" cy="41.5" r="2.5" fill="#3B82F6"/>
                                    </svg>

                                <span>Plan Better<br>Utilize Smarter<br>Grow Faster</span>
                            </div>
                        </div>
                    </div>
                    <div class="row header_count_row">
                        <div class="col-md-3">
                            <div class="report-box resources">
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
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="report-box clients">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-buildings" viewBox="0 0 16 16">
                                        <path d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V4.5a.5.5 0 0 1 .276-.447l8-4a.5.5 0 0 1 .487.022M6 8.694 1 10.36V15h5zM7 15h2v-1.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5V15h2V1.309l-7 3.5z"/>
                                        <path d="M2 11h1v1H2zm2 0h1v1H4zm-2 2h1v1H2zm2 0h1v1H4zm4-4h1v1H8zm2 0h1v1h-1zm-2 2h1v1H8zm2 0h1v1h-1zm2-2h1v1h-1zm0 2h1v1h-1zM8 7h1v1H8zm2 0h1v1h-1zm2 0h1v1h-1zM8 5h1v1H8zm2 0h1v1h-1zm2 0h1v1h-1zm0-2h1v1h-1z"/>
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
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="report-box total-hours">
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
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="report-box free-hours">
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
                            </div>
                        </div>
                    </div>
                </div>

                        @if (Auth::user()->hasRole('Admin'))
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
                                                                    <a href="{{ route('users.listing', ['work_type' => $label]) }}">{{ $count }}</a>
                                                                </td>
                                                                <td class="d-flex align-items-center justify-content-end gap-2">
                                                                    <progress class="{{ $workTypeBarClass }}" value="{{ $workTypePercentage }}" max="100">{{ number_format($workTypePercentage, 0) }}%</progress><span>{{ number_format($workTypePercentage, 0) }}%</span>
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
                                        <div class="card-body profit_loss_summery">
                                            <div class="summery_header">
                                                <h5><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.3.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M256 144C256 117.5 277.5 96 304 96L336 96C362.5 96 384 117.5 384 144L384 496C384 522.5 362.5 544 336 544L304 544C277.5 544 256 522.5 256 496L256 144zM64 336C64 309.5 85.5 288 112 288L144 288C170.5 288 192 309.5 192 336L192 496C192 522.5 170.5 544 144 544L112 544C85.5 544 64 522.5 64 496L64 336zM496 160L528 160C554.5 160 576 181.5 576 208L576 496C576 522.5 554.5 544 528 544L496 544C469.5 544 448 522.5 448 496L448 208C448 181.5 469.5 160 496 160z"/></svg>
                                                Profit / Loss Summary</h5>
                                            </div>
                                            <ul class="profit_loss_list">
                                                <li class="total_hours">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.3.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M192 160L192 144C192 99.8 278 64 384 64C490 64 576 99.8 576 144L576 160C576 190.6 534.7 217.2 474 230.7C471.6 227.9 469.1 225.2 466.6 222.7C451.1 207.4 431.1 195.8 410.2 187.2C368.3 169.7 313.7 160.1 256 160.1C234.1 160.1 212.7 161.5 192.2 164.2C192 162.9 192 161.5 192 160.1zM496 417L496 370.8C511.1 366.9 525.3 362.3 538.2 356.9C551.4 351.4 564.3 344.7 576 336.6L576 352C576 378.8 544.5 402.5 496 417zM496 321L496 288C496 283.5 495.6 279.2 495 275C510.5 271.1 525 266.4 538.2 260.8C551.4 255.2 564.3 248.6 576 240.5L576 255.9C576 282.7 544.5 306.4 496 320.9zM64 304L64 288C64 243.8 150 208 256 208C362 208 448 243.8 448 288L448 304C448 348.2 362 384 256 384C150 384 64 348.2 64 304zM448 400C448 444.2 362 480 256 480C150 480 64 444.2 64 400L64 384.6C75.6 392.7 88.5 399.3 101.8 404.9C143.7 422.4 198.3 432 256 432C313.7 432 368.3 422.3 410.2 404.9C423.4 399.4 436.3 392.7 448 384.6L448 400zM448 480.6L448 496C448 540.2 362 576 256 576C150 576 64 540.2 64 496L64 480.6C75.6 488.7 88.5 495.3 101.8 500.9C143.7 518.4 198.3 528 256 528C313.7 528 368.3 518.3 410.2 500.9C423.4 495.4 436.3 488.7 448 480.6z"/></svg>
                                                    <span>
                                                        <div class="title">Total Hours</div>
                                                        <div class="count">{{ number_format($profitLossSummary['total_hours'], 0) }}</div>
                                                        <div class="discription">{{ $profitLossSummary['internal_users_count'] }}
                                                            Internal Users</div>
                                                    </span>
                                                </li>
                                                <li class="assigned_hours">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.3.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M256 144C256 117.5 277.5 96 304 96L336 96C362.5 96 384 117.5 384 144L384 496C384 522.5 362.5 544 336 544L304 544C277.5 544 256 522.5 256 496L256 144zM64 336C64 309.5 85.5 288 112 288L144 288C170.5 288 192 309.5 192 336L192 496C192 522.5 170.5 544 144 544L112 544C85.5 544 64 522.5 64 496L64 336zM496 160L528 160C554.5 160 576 181.5 576 208L576 496C576 522.5 554.5 544 528 544L496 544C469.5 544 448 522.5 448 496L448 208C448 181.5 469.5 160 496 160z"/></svg>
                                                    <span>
                                                        <div class="title">Assigned Hours</div>
                                                        <div class="count">{{ number_format($profitLossSummary['assigned_hours'], 0) }}</div>
                                                        <div class="discription">Current Month Services</div>
                                                    </span>
                                                </li>
                                            </ul>

                                            <div class="stat-card">
                                                <!-- Stat Information -->
                                                <div class="stat-header">
                                                <div class="stat-value-row">
                                                    <!-- Trending Arrow Icon -->
                                                    <svg class="trend-icon" viewBox="0 0 24 24">
                                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                                    <polyline points="17 6 23 6 23 12"></polyline>
                                                    </svg>
                                                    <span class="percentage">0%</span>
                                                </div>

                                                <div class="stat-label-group">
                                                    <span class="stat-label">Profit</span>
                                                    <span class="stat-sublabel">0 hrs</span>
                                                </div>
                                                </div>

                                                <!-- Tooltip Badge -->
                                                <div class="tooltip-badge">0 hrs</div>

                                                <!-- Chart -->
                                                <div class="chart-container">
                                                <svg class="chart-svg" viewBox="0 0 480 150" preserveAspectRatio="none">
                                                    <defs>
                                                    <!-- Soft Gradient Fill Under Wave -->
                                                    <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                                        <stop offset="0%" stop-color="#00ba55" stop-opacity="0.28" />
                                                        <stop offset="100%" stop-color="#00ba55" stop-opacity="0.0" />
                                                    </linearGradient>
                                                    </defs>

                                                    <!-- Area fill under the line -->
                                                    <path 
                                                    d="M 94,120 
                                                        C 140,118 160,110 178,107 
                                                        C 220,100 245,68 268,68 
                                                        C 305,68 335,92 355,92 
                                                        C 390,92 410,72 426,68 
                                                        C 438,65 444,63 450,62 
                                                        L 450,150 L 94,150 Z" 
                                                    fill="url(#chartGradient)" 
                                                    />

                                                    <!-- Smooth Curve Stroke -->
                                                    <path 
                                                    d="M 94,120 
                                                        C 140,118 160,110 178,107 
                                                        C 220,100 245,68 268,68 
                                                        C 305,68 335,92 355,92 
                                                        C 390,92 410,72 426,68 
                                                        C 438,65 444,63 450,62" 
                                                    fill="none" 
                                                    stroke="#00ba55" 
                                                    stroke-width="2.5" 
                                                    stroke-linecap="round"
                                                    />

                                                    <!-- Data Dots -->
                                                    <circle cx="94" cy="120" r="3.5" fill="#00ba55" />
                                                    <circle cx="178" cy="107" r="3.5" fill="#00ba55" />
                                                    <circle cx="268" cy="68" r="3.5" fill="#00ba55" />
                                                    <circle cx="355" cy="92" r="3.5" fill="#00ba55" />
                                                    <circle cx="426" cy="68" r="3.5" fill="#00ba55" />
                                                    <circle cx="448" cy="62" r="3.5" fill="#00ba55" />
                                                </svg>
                                                </div>
                                            </div>
                                            
                                            
                                            <p class="text-muted f-12 mb-0 mt-1">
                                                Based on {{ $now->format('F Y') }} master hours and internal user capacity.
                                            </p>
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
                                                        <p>Internal users with 20 or more free hours in September 2026.   </p>
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
                                                                        <p><strong>No bench users found</strong>Bench shows internal users with 20 or more free hours in September 2026.   </p>
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
                        @endif





                        <div class="row align-items-start mt-4">
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
