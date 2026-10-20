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

        .rd-dashboard .rd-timers-panel { margin-top:18px; }
        .rd-dashboard .rd-timers-heading { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
        .rd-dashboard .rd-timers-heading h3 { margin:0; color:#13223f; font-size:17px; font-weight:700; }
        .rd-dashboard .rd-timers-count { display:inline-flex; align-items:center; gap:6px; padding:4px 9px; border-radius:99px; background:#e9f8f1; color:#12845c; font-size:11px; font-weight:700; }
        .rd-dashboard .rd-timers-count i { font-size:8px; }
        .rd-dashboard .rd-timer-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,340px),1fr)); gap:10px; }
        .rd-dashboard .rd-timer-card { display:grid; grid-template-columns:38px minmax(0,1fr) auto; align-items:center; gap:11px; min-width:0; padding:13px 14px; border:1px solid #dce8f5; border-radius:10px; background:linear-gradient(120deg,#f4f9ff,#fff 64%,#f3fbf7); text-decoration:none; transition:border-color .15s,transform .15s; }
        .rd-dashboard .rd-timer-card:hover { border-color:#8db9f5; transform:translateY(-1px); }
        .rd-dashboard .rd-timer-icon { display:flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px; background:#e5f0ff; color:#287cf0; font-size:17px; }
        .rd-dashboard .rd-timer-main { min-width:0; }
        .rd-dashboard .rd-timer-task { display:block; overflow:hidden; color:#173758; font-size:13px; font-weight:700; text-overflow:ellipsis; white-space:nowrap; }
        .rd-dashboard .rd-timer-context { display:flex; flex-wrap:wrap; gap:3px 10px; margin-top:4px; color:#73849a; font-size:10px; }
        .rd-dashboard .rd-timer-context strong { color:#50657e; font-weight:600; }
        .rd-dashboard .rd-timer-clock { display:flex; flex-direction:column; align-items:flex-end; color:#12845c; font-size:15px; font-weight:700; font-variant-numeric:tabular-nums; white-space:nowrap; }
        .rd-dashboard .rd-timer-clock small { color:#8292a6; font-size:9px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
        .rd-dashboard .rd-timers-empty { padding:15px; border:1px dashed #d7e2ef; border-radius:10px; color:#79889c; background:#fafcff; font-size:12px; }

        .rd-dashboard .rd-calendar-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
        .rd-dashboard .rd-calendar-title { margin:0; color:#13223f; font-size:20px; font-weight:700; }
        .rd-dashboard .rd-calendar-actions { display:flex; align-items:center; gap:7px; }
        .rd-dashboard .rd-calendar-actions a,.rd-dashboard .rd-calendar-toggle a { display:inline-flex; align-items:center; justify-content:center; min-height:36px; border:1px solid #dce6f3; border-radius:8px; padding:6px 11px; color:#526b88; background:#fff; text-decoration:none; font-size:12px; font-weight:600; }
        .rd-dashboard .rd-calendar-actions a:hover,.rd-dashboard .rd-calendar-toggle a:hover { color:#1769e8; background:#f3f8ff; }
        .rd-dashboard .rd-calendar-toggle { display:flex; align-items:center; gap:4px; margin-left:5px; border:1px solid #dce6f3; border-radius:9px; padding:3px; background:#f7f9fc; }
        .rd-dashboard .rd-calendar-toggle a { min-height:29px; border:0; padding:4px 12px; background:transparent; }
        .rd-dashboard .rd-calendar-toggle a.active { color:#1769e8; background:#fff; box-shadow:0 1px 3px rgba(24,55,92,.14); }
        .rd-dashboard .rd-month-grid { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); gap:6px; padding:4px; border:1px solid #e9eef5; border-radius:12px; background:linear-gradient(145deg,#f8fbff,#fff 55%,#f7faff); }
        .rd-dashboard .rd-calendar-weekday { padding:8px 4px 10px; color:#71839b; font-size:11px; font-weight:700; text-align:center; text-transform:uppercase; letter-spacing:.04em; }
        .rd-dashboard .rd-month-cell { min-width:0; min-height:104px; border:1px solid #e5ebf3; border-radius:9px; padding:8px 9px; background:rgba(255,255,255,.92); text-decoration:none; transition:border-color .15s,background .15s; }
        .rd-dashboard .rd-month-cell:hover { border-color:#8bbaff; background:#f5f9ff; }
        .rd-dashboard .rd-month-cell.is-today { border-color:#74aaff; background:#eef6ff; box-shadow:0 0 0 1px rgba(47,128,237,.08); }
        .rd-dashboard .rd-month-cell.is-empty { border-color:transparent; background:transparent; }
        .rd-dashboard .rd-month-day { display:flex; align-items:center; justify-content:space-between; margin-bottom:9px; color:#243d5b; font-size:13px; font-weight:700; }
        .rd-dashboard .rd-month-day a { display:inline-flex; align-items:center; justify-content:center; width:25px; height:25px; border-radius:50%; color:inherit; text-decoration:none; }
        .rd-dashboard .rd-month-cell.is-today .rd-month-day a { color:#fff; background:#287cf0; }
        .rd-dashboard .rd-month-dot { width:8px; height:8px; border-radius:50%; background:#d7dee8; }
        .rd-dashboard .rd-month-dot.has-activity { background:#20bf83; }
        .rd-dashboard .rd-month-bar { height:5px; margin:0 0 6px; border-radius:99px; background:#e8edf4; overflow:hidden; }
        .rd-dashboard .rd-month-bar span { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,#20c883,#16b981); }
        .rd-dashboard .rd-month-metric { margin-top:2px; color:#71839b; font-size:10px; line-height:1.35; }
        .rd-dashboard .rd-month-metric strong { color:#233b59; font-size:11px; }
        .rd-dashboard .rd-month-statuses { display:flex; flex-wrap:wrap; gap:3px 7px; max-height:28px; margin-top:5px; overflow:hidden; }
        .rd-dashboard .rd-month-status-count { display:inline-flex; align-items:center; gap:3px; color:#71839b; font-size:8px; font-weight:700; white-space:nowrap; }
        .rd-dashboard .rd-status-mark { flex:0 0 auto; width:6px; height:6px; border-radius:50%; background:#3d9bf5; }
        .rd-dashboard .rd-status-mark.in_progress { background:#2f6fe4; }
        .rd-dashboard .rd-status-mark.in_review { background:#f7b23b; }
        .rd-dashboard .rd-status-mark.blocked { background:#f0464f; }
        .rd-dashboard .rd-status-mark.completed { background:#22c58b; }
        .rd-dashboard .rd-week-scroll { overflow-x:auto; border:1px solid #e5ebf3; border-radius:11px; background:#fff; }
        .rd-dashboard .rd-week-board { min-width:1150px; }
        .rd-dashboard .rd-week-head,.rd-dashboard .rd-week-row { display:grid; grid-template-columns:214px minmax(936px,1fr); }
        .rd-dashboard .rd-week-head { position:sticky; top:0; z-index:2; border-bottom:1px solid #dce6f3; background:#f7faff; }
        .rd-dashboard .rd-week-head-label { padding:11px 13px; color:#71839b; font-size:10px; font-weight:700; text-transform:uppercase; }
        .rd-dashboard .rd-week-head-label { position:sticky; left:0; z-index:4; border-right:1px solid #e8edf4; background:#f7faff; }
        .rd-dashboard .rd-week-hours { display:grid; grid-template-columns:repeat(24,minmax(39px,1fr)); }
        .rd-dashboard .rd-week-hours span { padding:11px 2px; border-left:1px solid #e8edf4; color:#71839b; font-size:9px; text-align:center; white-space:nowrap; }
        .rd-dashboard .rd-week-row { min-height:98px; border-bottom:1px solid #e8edf4; }
        .rd-dashboard .rd-week-row:last-child { border-bottom:0; }
        .rd-dashboard .rd-week-row.is-today { background:#f5f9ff; }
        .rd-dashboard .rd-week-summary { display:flex; flex-direction:column; justify-content:center; min-width:0; padding:10px 13px; border-right:1px solid #e8edf4; }
        .rd-dashboard .rd-week-summary { position:sticky; left:0; z-index:3; background:#fff; }
        .rd-dashboard .rd-week-row.is-today .rd-week-summary { background:#f5f9ff; }
        .rd-dashboard .rd-week-summary-date { display:flex; align-items:center; justify-content:space-between; gap:5px; color:#183656; font-size:12px; font-weight:700; }
        .rd-dashboard .rd-week-summary-date a { color:inherit; text-decoration:none; }
        .rd-dashboard .rd-week-summary-metrics { display:flex; gap:10px; margin-top:5px; color:#71839b; font-size:10px; }
        .rd-dashboard .rd-week-summary-metrics strong { color:#183656; }
        .rd-dashboard .rd-week-progress { height:4px; margin-top:7px; border-radius:99px; background:#e8edf4; overflow:hidden; }
        .rd-dashboard .rd-week-progress span { display:block; height:100%; border-radius:inherit; background:#20bf83; }
        .rd-dashboard .rd-week-statuses { display:flex; flex-wrap:wrap; gap:3px 6px; max-height:25px; margin-top:5px; overflow:hidden; }
        .rd-dashboard .rd-week-status-count { display:inline-flex; align-items:center; gap:3px; color:#71839b; font-size:8px; font-weight:700; white-space:nowrap; }
        .rd-dashboard .rd-week-track { position:relative; min-height:98px; background-image:linear-gradient(to right,transparent calc(100% - 1px),#e8edf4 calc(100% - 1px)); background-size:calc(100% / 24) 100%; }
        .rd-dashboard .rd-week-task { position:absolute; top:14px; z-index:1; display:flex; align-items:center; min-width:20px; height:28px; overflow:visible; padding:0 6px; border:1px solid #c4dafa; border-radius:6px; background:#eff6ff; color:#245c9b; font-size:9px; font-weight:600; text-decoration:none; white-space:nowrap; box-shadow:0 1px 2px rgba(25,84,149,.08); }
        .rd-dashboard .rd-week-task.in_progress { border-color:#c4dafa; background:#eff6ff; color:#245c9b; }
        .rd-dashboard .rd-week-task.in_review { border-color:#f2dfb9; background:#fff9ec; color:#8d681d; }
        .rd-dashboard .rd-week-task.blocked { border-color:#efc9cc; background:#fff5f5; color:#a34b52; }
        .rd-dashboard .rd-week-task.completed { border-color:#c4e7d8; background:#f0faf5; color:#287959; }
        .rd-dashboard .rd-week-task.to_do { border-color:#d7dee8; background:#f6f8fb; color:#586b82; }
        .rd-dashboard .rd-week-task.rd-task-color-0 { border-color:#bfd6f7; background:#edf5ff; color:#285b96; }
        .rd-dashboard .rd-week-task.rd-task-color-1 { border-color:#bde6d5; background:#edfaf4; color:#267457; }
        .rd-dashboard .rd-week-task.rd-task-color-2 { border-color:#d8c8f3; background:#f5f0ff; color:#634895; }
        .rd-dashboard .rd-week-task.rd-task-color-3 { border-color:#f1d0ba; background:#fff4eb; color:#915b35; }
        .rd-dashboard .rd-week-task.rd-task-color-4 { border-color:#efc6d5; background:#fff0f5; color:#934b68; }
        .rd-dashboard .rd-week-task.rd-task-color-5 { border-color:#b9e1e8; background:#edfafc; color:#286975; }
        .rd-dashboard .rd-week-task.rd-task-color-6 { border-color:#e8dfa9; background:#fffbea; color:#796b26; }
        .rd-dashboard .rd-week-task-title { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .rd-dashboard .rd-week-task:hover,.rd-dashboard .rd-week-task:focus-visible { z-index:8; border-color:#a8bfda; }
        .rd-dashboard .rd-week-task::after { position:absolute; left:0; bottom:calc(100% + 8px); z-index:10; width:max-content; max-width:min(320px,70vw); padding:10px 12px; border:1px solid #d8e1ed; border-radius:9px; background:#fff; color:#253a54; content:attr(data-tooltip); font-size:11px; font-weight:500; line-height:1.45; white-space:normal; box-shadow:0 6px 20px rgba(26,48,77,.18); opacity:0; visibility:hidden; pointer-events:none; transition:opacity .12s ease; }
        .rd-dashboard .rd-week-task[data-tooltip-side="right"]::after { right:0; left:auto; }
        .rd-dashboard .rd-week-task:hover::after,.rd-dashboard .rd-week-task:focus-visible::after { opacity:1; visibility:visible; }
        .rd-dashboard .rd-week-task::before { position:absolute; left:12px; bottom:calc(100% + 2px); z-index:11; width:9px; height:9px; transform:rotate(45deg); border-right:1px solid #d8e1ed; border-bottom:1px solid #d8e1ed; background:#fff; content:""; opacity:0; visibility:hidden; }
        .rd-dashboard .rd-week-task[data-tooltip-side="right"]::before { right:12px; left:auto; }
        .rd-dashboard .rd-week-task:hover::before,.rd-dashboard .rd-week-task:focus-visible::before { opacity:1; visibility:visible; }
        .rd-dashboard .rd-week-empty { position:absolute; inset:0; display:flex; align-items:center; padding:0 12px; color:#a0adbd; font-size:10px; }
        @media (max-width: 767.98px) {
            .rd-dashboard .rd-month-grid { gap:3px; }
            .rd-dashboard .rd-month-cell { min-height:75px; padding:5px; }
            .rd-dashboard .rd-month-metric { font-size:9px; }
            .rd-dashboard .rd-week-head,.rd-dashboard .rd-week-row { grid-template-columns:160px minmax(936px,1fr); }
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
                                    @php
                                        $bars = $stat['spark']['bars'];
                                    @endphp
                                    <svg class="rd-spark is-{{ $stat['spark']['kind'] }}" viewBox="0 0 56 30" aria-hidden="true">
                                        @foreach ($bars as $i => $height)
                                            @php
                                                $h = max(3, round($height / 100 * 30, 1));
                                            @endphp
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
                                    <span class="rd-note">{{ ($isClientDashboard ?? false) ? 'Tasks in your projects' : 'All assigned tasks by current status' }}</span>
                                </div>
                                <div class="rd-panel-body">
                                    @if ($taskTotal === 0)
                                        <p class="rd-empty">{{ ($isClientDashboard ?? false) ? 'There are no tasks in your projects yet.' : 'You have no assigned tasks yet.' }}</p>
                                    @else
                                    <div class="rd-status">
                                        <div class="rd-donut">
                                            {{-- radius 15.915 gives a circumference of 100, so each dash length is a percentage --}}
                                            <svg viewBox="0 0 42 42" role="img" aria-label="Tasks by status">
                                                <circle cx="21" cy="21" r="15.915" stroke="#eef2f8"></circle>
                                                @php
                                                    $offset = 0;
                                                @endphp
                                                @foreach ($taskStatuses as $status)
                                                    @php
                                                        $share = $status['count'] / $taskTotal * 100;
                                                    @endphp
                                                    <circle cx="21" cy="21" r="15.915" stroke="{{ $status['color'] }}"
                                                        stroke-dasharray="{{ round(max($share - 0.6, 0), 3) }} {{ round(100 - max($share - 0.6, 0), 3) }}"
                                                        stroke-dashoffset="{{ round(-$offset, 3) }}"></circle>
                                                    @php
                                                        $offset += $share;
                                                    @endphp
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
                @unless ($isClientDashboard ?? false)
                    @php
                        $previousCalendarDate = $calendarView === 'month'
                            ? $calendarDate->copy()->subMonthNoOverflow()->startOfMonth()
                            : $calendarStart->copy()->subWeek();
                        $nextCalendarDate = $calendarView === 'month'
                            ? $calendarDate->copy()->addMonthNoOverflow()->startOfMonth()
                            : $calendarStart->copy()->addWeek();
                        $monthLeadingBlanks = $calendarView === 'month' ? $calendarStart->dayOfWeekIso - 1 : 0;
                    @endphp
                    <section class="card shadow-sm rd-timers-panel" aria-labelledby="active-timers-heading">
                        <div class="card-body">
                            <div class="rd-timers-heading">
                                <h3 id="active-timers-heading">Currently tracking</h3>
                                <span class="rd-timers-count"><i class="bi bi-circle-fill"></i>{{ count($activeTimers) }} {{ count($activeTimers) === 1 ? 'active timer' : 'active timers' }}</span>
                            </div>
                            @if (count($activeTimers))
                                <div class="rd-timer-grid">
                                    @foreach ($activeTimers as $timer)
                                        <a class="rd-timer-card" href="{{ route('tasks.show', $timer['task_id']) }}" title="Open task: {{ $timer['task'] }}">
                                            <span class="rd-timer-icon"><i class="bi bi-stopwatch"></i></span>
                                            <span class="rd-timer-main">
                                                <span class="rd-timer-task">{{ $timer['task'] }}</span>
                                                <span class="rd-timer-context">
                                                    <span>Story <strong>{{ $timer['story'] ?: '—' }}</strong></span>
                                                    <span>Project <strong>{{ $timer['project'] ?: '—' }}</strong></span>
                                                    <span>Client <strong>{{ $timer['client'] ?: '—' }}</strong></span>
                                                </span>
                                            </span>
                                            <span class="rd-timer-clock"><span data-live-timer data-seconds="{{ $timer['seconds'] }}">{{ sprintf('%02d:%02d:%02d', intdiv($timer['seconds'], 3600), intdiv($timer['seconds'] % 3600, 60), $timer['seconds'] % 60) }}</span><small>elapsed</small></span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="rd-timers-empty">No tasks are being tracked right now.</div>
                            @endif
                        </div>
                    </section>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="rd-calendar-toolbar mb-3">
                                        <div>
                                            <h3 class="rd-calendar-title">{{ $calendarView === 'month' ? $calendarDate->format('F Y') : $calendarStart->format('j M') . ' – ' . $calendarEnd->format('j M Y') }}</h3>
                                            <span class="rd-note">Daily work, tracked time, and completed tasks</span>
                                        </div>
                                        <div class="rd-calendar-actions">
                                            <a href="{{ route('dashboard', ['calendar_view' => $calendarView, 'calendar_date' => $previousCalendarDate->toDateString()]) }}" aria-label="Previous {{ $calendarView }}"><i class="bi bi-chevron-left"></i></a>
                                            <a href="{{ route('dashboard', ['calendar_view' => $calendarView, 'calendar_date' => $nextCalendarDate->toDateString()]) }}" aria-label="Next {{ $calendarView }}"><i class="bi bi-chevron-right"></i></a>
                                            <div class="rd-calendar-toggle" role="tablist" aria-label="Activity view">
                                                <a class="{{ $calendarView === 'month' ? 'active' : '' }}" href="{{ route('dashboard', ['calendar_view' => 'month', 'calendar_date' => $calendarDate->toDateString()]) }}">Month</a>
                                                <a class="{{ $calendarView === 'week' ? 'active' : '' }}" href="{{ route('dashboard', ['calendar_view' => 'week', 'calendar_date' => $calendarDate->toDateString()]) }}">Week</a>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($calendarView === 'month')
                                        @php
                                            $monthMaxTracked = max(1, collect($calendarDays)->max('tracked_seconds'));
                                        @endphp
                                        <div class="rd-month-grid">
                                            @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                                                <div class="rd-calendar-weekday">{{ $weekday }}</div>
                                            @endforeach
                                            @for ($blank = 0; $blank < $monthLeadingBlanks; $blank++)
                                                <div class="rd-month-cell is-empty" aria-hidden="true"></div>
                                            @endfor
                                            @foreach ($calendarDays as $day)
                                                <div class="rd-month-cell {{ $day['date']->isToday() ? 'is-today' : '' }}">
                                                    <div class="rd-month-day">
                                                        <a href="{{ route('dashboard', ['calendar_view' => 'week', 'calendar_date' => $day['date']->toDateString()]) }}" title="Open week of {{ $day['date']->format('j F Y') }}">{{ $day['date']->day }}</a>
                                                        <i class="rd-month-dot {{ $day['tracked_seconds'] > 0 ? 'has-activity' : '' }}"></i>
                                                    </div>
                                                    <div class="rd-month-bar"><span style="width: {{ min(100, (int) round($day['tracked_seconds'] * 100 / $monthMaxTracked)) }}%"></span></div>
                                                    <div class="rd-month-metric"><strong>{{ intdiv($day['tracked_seconds'], 3600) }}h {{ intdiv($day['tracked_seconds'] % 3600, 60) }}m</strong> tracked</div>
                                                    <div class="rd-month-metric"><strong>{{ $day['tracked_task_count'] }}</strong> tasks tracked</div>
                                                    <div class="rd-month-statuses" aria-label="Tracked task counts by status">
                                                        @foreach (['completed' => 'Done', 'in_progress' => 'Progress', 'in_review' => 'Review', 'blocked' => 'Blocked', 'to_do' => 'To do'] as $statusKey => $statusLabel)
                                                            @if ($day['tracked_status_counts'][$statusKey] > 0)
                                                                <span class="rd-month-status-count"><i class="rd-status-mark {{ $statusKey }}"></i>{{ $statusLabel }} {{ $day['tracked_status_counts'][$statusKey] }}</span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="rd-week-scroll" data-shift-start-minute="{{ $weeklyShiftStartMinute }}">
                                            <div class="rd-week-board">
                                                <div class="rd-week-head">
                                                    <div class="rd-week-head-label">Day · summary</div>
                                                    <div class="rd-week-hours">
                                                        @foreach (range(0, 23) as $hour)
                                                            <span>{{ $hour === 0 ? '12 AM' : ($hour < 12 ? $hour . ' AM' : ($hour === 12 ? '12 PM' : ($hour - 12) . ' PM')) }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @foreach ($calendarDays as $day)
                                                    <article class="rd-week-row {{ $day['date']->isToday() ? 'is-today' : '' }}">
                                                        <div class="rd-week-summary">
                                                            <div class="rd-week-summary-date">
                                                                <a href="{{ route('dashboard', ['calendar_view' => 'week', 'calendar_date' => $day['date']->toDateString()]) }}">{{ $day['date']->format('D, j M') }}</a>
                                                                <span>{{ \App\Support\DurationFormatter::fromHours($day['estimated_hours']) }} est.</span>
                                                            </div>
                                                            <div class="rd-week-summary-metrics">
                                                                <span><strong>{{ intdiv($day['tracked_seconds'], 3600) }}h {{ intdiv($day['tracked_seconds'] % 3600, 60) }}m</strong> tracked</span>
                                                                <span><strong>{{ $day['tracked_task_count'] }}</strong> tasks</span>
                                                            </div>
                                                            <div class="rd-week-progress"><span style="width: {{ min(100, (int) round($day['tracked_seconds'] * 100 / 28800)) }}%"></span></div>
                                                            <div class="rd-week-statuses" aria-label="Tracked task counts by status">
                                                                @foreach (['completed' => 'Done', 'in_progress' => 'Progress', 'in_review' => 'Review', 'blocked' => 'Blocked', 'to_do' => 'To do'] as $statusKey => $statusLabel)
                                                                    @if ($day['tracked_status_counts'][$statusKey] > 0)
                                                                        <span class="rd-week-status-count"><i class="rd-status-mark {{ $statusKey }}"></i>{{ $statusLabel }} {{ $day['tracked_status_counts'][$statusKey] }}</span>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <div class="rd-week-track">
                                                            @forelse ($day['tracked_tasks'] as $trackedTask)
                                                                <a class="rd-week-task {{ $trackedTask['status'] }} rd-task-color-{{ $trackedTask['color_index'] }}" href="{{ route('tasks.show', $trackedTask['task_id']) }}" style="top: {{ $loop->index % 2 === 0 ? 14 : 49 }}px; left: {{ min(100, $trackedTask['start_minute'] * 100 / 1440) }}%; width: {{ min(100 - ($trackedTask['start_minute'] * 100 / 1440), max(.4, $trackedTask['duration_minutes'] * 100 / 1440)) }}%" data-tooltip="{{ $trackedTask['title'] }} · {{ $trackedTask['start_label'] }}–{{ $trackedTask['end_label'] }} · {{ ucfirst(str_replace('_', ' ', $trackedTask['status'])) }}" data-tooltip-side="{{ $trackedTask['start_minute'] > 840 ? 'right' : 'left' }}" aria-label="{{ $trackedTask['title'] }}, {{ $trackedTask['start_label'] }} to {{ $trackedTask['end_label'] }}">
                                                                    {{ $trackedTask['start_label'] }} · <span class="rd-week-task-title">{{ $trackedTask['title'] }}</span>
                                                                </a>
                                                            @empty
                                                                <span class="rd-week-empty">No tracked task time</span>
                                                            @endforelse
                                                        </div>
                                                    </article>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endunless
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const configureWeeklyTimeline = () => {
                document.querySelectorAll('.rd-week-scroll[data-shift-start-minute]').forEach((scroller) => {
                    const board = scroller.querySelector('.rd-week-board');
                    const summary = board?.querySelector('.rd-week-head-label');
                    if (!board || !summary || !scroller.clientWidth) return;

                    const previousHourWidth = Number(board.dataset.hourWidth || 0);
                    const previousScrollHour = previousHourWidth
                        ? scroller.scrollLeft / previousHourWidth
                        : Number(scroller.dataset.shiftStartMinute || 540) / 60;
                    const summaryWidth = summary.getBoundingClientRect().width || 214;
                    const visibleTimelineWidth = Math.max(360, scroller.clientWidth - summaryWidth);
                    const hourWidth = visibleTimelineWidth / 12;
                    const boardWidth = summaryWidth + hourWidth * 24;

                    board.dataset.hourWidth = String(hourWidth);
                    board.style.width = `${boardWidth}px`;
                    board.style.minWidth = `${boardWidth}px`;
                    board.querySelectorAll('.rd-week-head, .rd-week-row').forEach((row) => {
                        row.style.gridTemplateColumns = `${summaryWidth}px ${hourWidth * 24}px`;
                    });
                    board.querySelectorAll('.rd-week-hours').forEach((hours) => {
                        hours.style.gridTemplateColumns = `repeat(24, ${hourWidth}px)`;
                    });
                    board.querySelectorAll('.rd-week-track').forEach((track) => {
                        track.style.backgroundSize = `${hourWidth}px 100%`;
                    });
                    scroller.scrollLeft = previousScrollHour * hourWidth;
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', configureWeeklyTimeline, { once: true });
            } else {
                configureWeeklyTimeline();
            }
            window.addEventListener('resize', configureWeeklyTimeline);
        })();

        (() => {
            const timers = document.querySelectorAll('[data-live-timer]');
            if (!timers.length) return;

            const render = (element) => {
                const seconds = Math.max(0, Number(element.dataset.seconds || 0));
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                const remainder = seconds % 60;
                element.textContent = [hours, minutes, remainder].map((part) => String(part).padStart(2, '0')).join(':');
            };

            timers.forEach((timer) => {
                render(timer);
                window.setInterval(() => {
                    timer.dataset.seconds = Number(timer.dataset.seconds || 0) + 1;
                    render(timer);
                }, 1000);
            });
        })();
    </script>
@endpush
