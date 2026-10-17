@extends('layouts.app')
@section('title')
    Dashboard
@endsection

@push('styles')
    <style>
        /*
         * Resource dashboard. The page frame, the header band, the four count cards and the
         * panels use the same classes as the admin dashboard (dashboard_content_header,
         * report-box, chart_box_row, card, summery_header), so sizes, shadows and spacing
         * come from one place. Only the contents of the panels are styled here.
         * The figures come from HomeController::resourceDashboard().
         */
        .rd-dashboard .report-box .icon i {
            font-size: 26px;
            line-height: 1;
        }

        /* the month-on-month change sits in the card's top right corner, so it never changes the card's height */
        .rd-dashboard .report-box {
            align-items: center;
            min-height: 85px;
            padding-right: 84px; /* room for the mini graph */
        }

        .rd-dashboard .report-box .icon {
            flex: 0 0 55px;
        }

        .rd-dashboard .report_content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1px;
            min-width: 0;
        }

        .rd-dashboard .report_content span.report_title {
            display: block;
            line-height: 1.3;
        }

        .rd-dashboard .report_content h4 {
            margin: 0;
            line-height: 1.2;
        }

        /* mini bar graph, bottom right */
        .rd-dashboard .rd-spark {
            position: absolute;
            right: 16px;
            bottom: 14px;
            width: 56px;
            height: 30px;
        }

        .rd-dashboard .rd-spark rect {
            fill: #c5cdda;
        }

        .rd-dashboard .rd-spark.is-up rect {
            fill: #04bd47;
        }

        .rd-dashboard .rd-spark.is-down rect {
            fill: #ef4444;
        }

        .rd-dashboard .rd-trend {
            position: absolute;
            top: 12px;
            right: 14px;
            display: inline-flex;
            align-items: center;
            padding: 2px 8px 2px 4px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 700;
            line-height: 18px;
            white-space: nowrap;
        }

        .rd-dashboard .rd-trend i {
            font-size: 18px;
            line-height: 1;
        }

        .rd-dashboard .rd-trend.is-up {
            background: #e3f8ec;
            color: #04bd47;
        }

        .rd-dashboard .rd-trend.is-down {
            background: #fde8e8;
            color: #e11d1d;
        }

        .rd-dashboard .rd-trend.is-none {
            padding-left: 8px;
            background: #eef1f6;
            color: #7a879c;
            font-weight: 600;
        }

        .rd-dashboard .report-box.resources .icon i {
            color: #4581fb;
        }

        .rd-dashboard .report-box.clients .icon i {
            color: #a367fd;
        }

        .rd-dashboard .report-box.total-hours .icon i {
            color: #05cd66;
        }

        .rd-dashboard .report-box.free-hours .icon i {
            color: #ff7926;
        }

        .rd-dashboard .report_content span.rating i {
            font-size: 20px;
            line-height: 1;
            vertical-align: middle;
            margin: 0 -3px 0 -4px;
        }

        .rd-dashboard .summery_header a {
            font-size: 12px;
            font-weight: 600;
            color: #047cff;
            text-decoration: none;
        }

        .rd-dashboard .summery_header a:hover {
            text-decoration: underline;
        }

        .rd-dashboard .summery_header h5 i {
            color: #047cff;
            margin-right: 6px;
        }

        .rd-dashboard .rd-panel-body {
            padding-top: 14px;
            font-size: 13px;
            color: #46536b;
        }

        /* Task status */
        .rd-dashboard .rd-status {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .rd-dashboard .rd-donut {
            position: relative;
            flex: 0 0 180px;
            width: 180px;
            height: 180px;
        }

        .rd-dashboard .rd-donut svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .rd-dashboard .rd-donut circle {
            fill: none;
            stroke-width: 5.2;
        }

        .rd-dashboard .rd-donut-center {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .rd-dashboard .rd-donut-center strong {
            font-size: 26px;
            font-weight: 700;
            line-height: 1;
            color: #13223f;
        }

        .rd-dashboard .rd-donut-center span {
            margin-top: 4px;
            font-size: 12px;
            font-weight: 600;
            color: #46536b;
        }

        .rd-dashboard .rd-list {
            flex: 1 1 auto;
            min-width: 0;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .rd-dashboard .rd-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 0;
            border-bottom: 1px solid #dddddd61;
        }

        .rd-dashboard .rd-list li:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .rd-dashboard .rd-dot {
            flex: 0 0 10px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .rd-dashboard .rd-grow {
            flex: 1 1 auto;
            min-width: 0;
        }

        .rd-dashboard .rd-strong {
            font-weight: 700;
            color: #13223f;
        }

        .rd-dashboard .rd-muted {
            color: #7a879c;
            white-space: nowrap;
        }

        .rd-dashboard .rd-legend-count {
            min-width: 22px;
            text-align: right;
        }

        .rd-dashboard .rd-legend-share {
            min-width: 42px;
            text-align: right;
        }

        /* Project progress */
        .rd-dashboard .rd-progress li {
            border-bottom: 0;
            padding: 10px 0;
        }

        .rd-dashboard .rd-progress-name {
            flex: 1 1 46%;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .rd-dashboard .rd-progress-bar {
            flex: 1 1 34%;
            height: 7px;
            border-radius: 99px;
            background: #e9edf4;
            overflow: hidden;
        }

        .rd-dashboard .rd-progress-bar span {
            display: block;
            height: 100%;
            border-radius: 99px;
        }

        .rd-dashboard .rd-progress-value {
            flex: 0 0 36px;
            text-align: right;
        }


        .rd-dashboard .rd-note {
            font-size: 11px;
            font-weight: 600;
            color: #7a879c;
        }

        .rd-dashboard .rd-empty {
            margin: 0;
            padding: 28px 0;
            text-align: center;
            color: #7a879c;
        }

        .rd-dashboard a.rd-progress-name {
            color: inherit;
            text-decoration: none;
        }

        .rd-dashboard a.rd-progress-name:hover {
            color: #047cff;
        }

        @media (max-width: 575.98px) {
            .rd-dashboard .rd-status {
                flex-direction: column;
                gap: 16px;
            }

            .rd-dashboard .rd-list {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid rd-dashboard">
        <div class="row">
            <div class="col-md-12">
                {{-- Header band and count cards: same structure as the admin dashboard --}}
                <div class="dashboard_content_header header-wrapper">
                    <div class="row header-row">
                        <div class="col-md-12">
                            <h2>Dashboard</h2>
                            <p>Here's what's happening with your projects.</p>
                        </div>
                    </div>
                    <div class="row header_count_row">
                        @foreach ($stats as $stat)
                            <div class="col-sm-6 col-md-3">
                                <div class="report-box {{ $stat['tone'] }}">
                                    <div class="icon">
                                        <i class="bi {{ $stat['icon'] }}"></i>
                                    </div>
                                    <div class="report_content">
                                        <span class="report_title">{{ $stat['label'] }}</span>
                                        <h4>{{ $stat['value'] }}</h4>
                                        @if ($stat['trend'] !== false)
                                            @if ($stat['trend'] === null)
                                                <span class="rd-trend is-none" title="Nothing from last month to compare with">No data</span>
                                            @else
                                                <span class="rd-trend {{ $stat['trend'] >= 0 ? 'is-up' : 'is-down' }}" title="vs last month">
                                                    <i class="bi {{ $stat['trend'] >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>{{ abs($stat['trend']) }}%
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    @php($bars = $stat['spark']['bars'])
                                    <svg class="rd-spark is-{{ $stat['spark']['kind'] }}" viewBox="0 0 56 30" aria-hidden="true">
                                        @foreach ($bars as $i => $height)
                                            @php($h = max(3, round($height / 100 * 30, 1)))
                                            <rect x="{{ $i * 8 }}" y="{{ 30 - $h }}" width="5" height="{{ $h }}" rx="1.5"
                                                opacity="{{ round(0.45 + 0.55 * ($i + 1) / count($bars), 2) }}"></rect>
                                        @endforeach
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row chart_box_row">
                    {{-- Task status --}}
                    <div class="col-md-12 col-lg-7">
                        <div class="card shadow-sm w-100 h-100">
                            <div class="card-body">
                                <div class="summery_header">
                                    <h5><i class="bi bi-pie-chart-fill"></i>Task Status</h5>
                                    <span class="rd-note">{{ ($isClientDashboard ?? false) ? 'Tasks in your projects' : 'Open tasks, and those completed this month' }}</span>
                                </div>
                                <div class="rd-panel-body">
                                    @if ($taskTotal === 0)
                                        <p class="rd-empty">{{ ($isClientDashboard ?? false) ? 'There are no tasks in your projects yet.' : 'You have no open tasks, and none completed this month.' }}</p>
                                    @else
                                    <div class="rd-status">
                                        <div class="rd-donut">
                                            {{-- radius 15.915 gives a circumference of 100, so each dash length is a percentage --}}
                                            <svg viewBox="0 0 42 42" role="img" aria-label="Tasks by status">
                                                <circle cx="21" cy="21" r="15.915" stroke="#eef2f8"></circle>
                                                @php($offset = 0)
                                                @foreach ($taskStatuses as $status)
                                                    @php($share = $status['count'] / $taskTotal * 100)
                                                    <circle cx="21" cy="21" r="15.915" stroke="{{ $status['color'] }}"
                                                        stroke-dasharray="{{ round(max($share - 0.6, 0), 3) }} {{ round(100 - max($share - 0.6, 0), 3) }}"
                                                        stroke-dashoffset="{{ round(-$offset, 3) }}"></circle>
                                                    @php($offset += $share)
                                                @endforeach
                                            </svg>
                                            <div class="rd-donut-center">
                                                <strong>{{ $taskTotal }}</strong>
                                                <span>Tasks</span>
                                            </div>
                                        </div>

                                        <ul class="rd-list">
                                            @foreach ($taskStatuses as $status)
                                                <li>
                                                    <span class="rd-dot" style="background: {{ $status['color'] }}"></span>
                                                    <span class="rd-grow">{{ $status['label'] }}</span>
                                                    <span class="rd-strong rd-legend-count">{{ $status['count'] }}</span>
                                                    <span class="rd-muted rd-legend-share">({{ round($status['count'] / $taskTotal * 100) }}%)</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Project progress --}}
                    <div class="col-md-12 col-lg-5 mt-3 mt-lg-0">
                        <div class="card shadow-sm w-100 h-100">
                            <div class="card-body">
                                <div class="summery_header">
                                    <h5><i class="bi bi-kanban-fill"></i>Project Progress ({{ $projectsCount }})</h5>
                                    <a href="{{ route('projects.index') }}">View All</a>
                                </div>
                                <div class="rd-panel-body">
                                    @if (empty($projectProgress))
                                        <p class="rd-empty">{{ ($isClientDashboard ?? false) ? 'You do not have any projects yet.' : 'You are not a member of any project yet.' }}</p>
                                    @else
                                        <ul class="rd-list rd-progress">
                                            @foreach ($projectProgress as $project)
                                                <li>
                                                    <span class="rd-dot" style="background: {{ $project['dot'] }}"></span>
                                                    <a class="rd-progress-name" href="{{ route('projects.show', $project['id']) }}"
                                                        title="{{ $project['name'] }}">{{ $project['name'] }}</a>
                                                    <span class="rd-progress-bar">
                                                        <span style="width: {{ $project['progress'] }}%; background: {{ $project['bar'] }}"></span>
                                                    </span>
                                                    <span class="rd-progress-value">{{ $project['progress'] }}%</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
