@extends('layouts.app')
@section('title')
    {{ $task->title }} - Task Details
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/project-module.css') }}">
    <style>
        /* the action side keeps its natural width, so every button stays on one row;
           a long title wraps inside the left side instead */
        .task-detail .pm-project-head.track_timer {
            gap: 16px 24px;
            flex-wrap: wrap;
        }

        /* the title side always keeps a readable width; when the action buttons
           do not fit beside it they drop to their own row */
        .task-detail .pm-project-head.track_timer > .left_action_btn {
            flex: 1 1 320px;
            min-width: min(100%, 280px);
        }

        /* the icon keeps its square shape next to a long, wrapping title */
        .task-detail .pm-project-head.track_timer .pm-project-icon {
            flex: 0 0 64px;
            width: 64px;
            height: 64px;
        }

        .task-detail .pm-project-head.track_timer > .left_action_btn {
            gap: 16px;
        }

        .task-detail .pm-project-head.track_timer > .left_action_btn > div:last-child {
            flex: 1 1 0;
            min-width: 0;
        }

        .task-detail .pm-project-head.track_timer > .left_action_btn h1 {
            font-size: 1.6rem;
            line-height: 1.3;
            overflow-wrap: break-word;
        }

        .task-detail .pm-project-head.track_timer > .left_action_btn h1 .pm-badge {
            vertical-align: middle;
        }

        .task-detail .task-title-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px 12px;
        }

        .task-detail .task-title-row .pm-badge {
            margin-left: 0;
        }

        .task-detail .task-status-dropdown {
            vertical-align: middle;
            flex-shrink: 0;
        }

        .task-detail .task-title-text {
            min-width: 0;
        }

        .task-detail .task-status-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 0;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
            flex-shrink: 0;
            cursor: pointer;
        }

        .task-detail .task-status-toggle .bi {
            font-size: 0.65rem;
        }

        .task-detail .task-status-toggle.is-saving {
            opacity: .6;
            pointer-events: none;
        }

        .task-detail ul.task-status-menu.dropdown-menu {
            width: auto;
            min-width: 160px;
            padding: 4px 0;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .task-detail ul.task-status-menu .task-status-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            font-size: 0.9rem;
            color: #334155;
        }

        .task-detail ul.task-status-menu .task-status-option:hover,
        .task-detail ul.task-status-menu .task-status-option.active {
            background: #f1f5f9;
            color: #0f172a;
        }

        .task-status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
            background: #9aa4b2;
        }
        .task-status-dot-to_do { background: #8b95a5; }
        .task-status-dot-in_progress { background: #f59e0b; }
        .task-status-dot-in_review { background: #8b5cf6; }
        .task-status-dot-completed { background: #22c55e; }
        .task-status-dot-on_hold { background: #ef4444; }

        /* timer label stacked above the buttons: the group is only as wide as the
           button row, so it sits beside the title whenever there is room */
        .task-detail .pm-project-head.track_timer > .right_action_btn {
            flex: 0 0 auto;
            flex-direction: column;
            flex-wrap: nowrap;
            align-items: flex-end;
            gap: 10px;
            margin-left: auto;
        }

        .task-detail .pm-project-head.track_timer > .right_action_btn > p.timer {
            width: auto;
            margin: 0;
        }

        .task-detail .pm-project-head.track_timer > .right_action_btn p.timer {
            text-align: right;
        }

        .task-detail .pm-project-head.track_timer > .right_action_btn > .right_action_btn {
            flex-wrap: nowrap;
            align-items: center;
            justify-content: flex-end;
        }

        .task-detail .pm-project-head.track_timer button.start_btn,
        .task-detail .pm-project-head.track_timer button.stop_btn,
        .task-detail .pm-project-head.track_timer button.track_btn {
            white-space: nowrap;
            height: 46px;
            font-size: 16px;
            line-height: 1;
        }

        .task-detail .pm-project-head.track_timer button.track_btn {
            padding: 0 25px;
        }

        .task-detail .pm-project-head.track_timer button.start_btn,
        .task-detail .pm-project-head.track_timer button.stop_btn {
            padding-left: 20px;
        }

        .task-detail .pm-project-head.track_timer button.start_btn span,
        .task-detail .pm-project-head.track_timer button.stop_btn span {
            align-self: stretch;
            display: flex;
            align-items: center;
            padding: 0 13px;
        }

        .task-detail .pm-project-head.track_timer button.start_btn:disabled,
        .task-detail .pm-project-head.track_timer button.stop_btn:disabled {
            opacity: .75;
            cursor: not-allowed;
        }

        /* smaller screens: everything lines up on the left */
        @media (max-width: 1199.98px) {
            .task-detail .pm-project-head.track_timer > .right_action_btn {
                align-items: flex-start;
                margin-left: 0;
            }

            .task-detail .pm-project-head.track_timer > .right_action_btn > p.timer {
                text-align: left;
            }

            .task-detail .pm-project-head.track_timer > .right_action_btn > .right_action_btn {
                justify-content: flex-start;
            }
        }

        /* narrow screens: title on top, actions below and free to wrap */
        @media (max-width: 991.98px) {
            .task-detail .pm-project-head.track_timer {
                flex-wrap: wrap;
            }

            .task-detail .pm-project-head.track_timer > .right_action_btn {
                flex: 1 1 100%;
                align-items: flex-start;
                margin-left: 0;
            }

            .task-detail .pm-project-head.track_timer > .right_action_btn > .right_action_btn {
                flex-wrap: wrap;
            }
        }

        .task-detail .task-head-tools {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* square companions to the Start/Stop Timer button: same height, radius and blue */
        .task-detail .task-tool-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border: 0;
            border-radius: 8px;
            font-size: 19px;
            text-decoration: none;
            transition: background-color .15s ease, color .15s ease;
        }

        .task-detail .task-tool-btn.is-edit {
            background: #e7f0ff;
            color: #0d6efd;
        }

        .task-detail .task-tool-btn.is-edit:hover {
            background: #0d6efd;
            color: #fff;
        }

        .task-detail .task-tool-btn.is-back {
            background: #eef1f6;
            color: #46536b;
        }

        .task-detail .task-tool-btn.is-back:hover {
            background: #46536b;
            color: #fff;
        }

        .task-detail .task-description {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            min-height: 250px;
            background: #fff;
            overflow-wrap: anywhere;
        }

        /* Chat tab */
        .task-detail .task-chat-card {
            background: #fff;
            border: 1px solid #e6edf7;
            border-radius: 12px;
            overflow: hidden;
        }

        .task-detail .task-chat-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-height: 140px;
            max-height: 400px;
            overflow-y: auto;
            padding: 20px;
        }

        .task-detail .task-chat-composer {
            margin: 0;
            border-top: 1px solid #e6edf7;
            background: #f8faff;
            padding: 16px 20px;
        }

        .task-detail .task-chat-box {
            background: #fff;
            border: 1px solid #d5deeb;
            border-radius: 10px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .task-detail .task-chat-box:focus-within {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, .12);
        }

        .task-detail .task-chat-toolbar {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 6px 8px;
            border-bottom: 1px solid #eef1f6;
        }

        .task-detail .task-chat-toolbar button {
            width: 32px;
            height: 32px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #46536b;
            font-size: 15px;
        }

        .task-detail .task-chat-toolbar button:hover {
            background: #eef4ff;
            color: #0d6efd;
        }

        .task-detail .task-chat-box textarea {
            display: block;
            width: 100%;
            min-height: 0;
            height: 74px;
            max-height: 220px;
            border: 0;
            border-radius: 0 0 10px 10px;
            padding: 12px 14px;
            resize: none;
            outline: none;
            overflow-y: auto;
            line-height: 1.5;
            text-align: left;
        }

        .task-detail .task-chat-body ul,
        .task-detail .task-chat-body ol {
            margin: 0 0 0.5rem;
            padding-left: 1.3rem;
        }

        .task-detail .task-chat-body ul:last-child,
        .task-detail .task-chat-body ol:last-child {
            margin-bottom: 0;
        }

        .task-detail .task-chat-body a {
            overflow-wrap: anywhere;
        }

        .task-detail .task-chat-composer .task-chat-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 12px;
        }

        .task-detail .task-chat-composer .task-chat-hint {
            color: #8a97ab;
            font-size: 13px;
        }

        .task-detail .task-chat-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            max-width: 80%;
        }

        .task-detail .task-chat-item.is-own {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .task-detail .task-chat-avatar {
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #ece6fd;
            color: #7d34fd;
            font-weight: 700;
        }

        .task-detail .task-chat-item.is-own .task-chat-avatar {
            background: #0d6efd;
            color: #fff;
        }

        .task-detail .task-chat-bubble {
            min-width: 0;
            background: #f4f7fc;
            border-radius: 4px 14px 14px 14px;
            padding: 10px 16px 12px;
        }

        .task-detail .task-chat-item.is-own .task-chat-bubble {
            background: #e7f0ff;
            border-radius: 14px 4px 14px 14px;
        }

        .task-detail .task-chat-meta {
            display: flex;
            align-items: baseline;
            gap: 10px;
            margin-bottom: 4px;
        }

        .task-detail .task-chat-meta strong {
            color: #00001e;
            font-size: 14px;
        }

        .task-detail .task-chat-meta span {
            color: #8a97ab;
            font-size: 12px;
            white-space: nowrap;
        }

        .task-detail .task-chat-body {
            color: #46536b;
            overflow-wrap: anywhere;
        }

        .task-detail .task-chat-body p:last-child {
            margin-bottom: 0;
        }

        .task-detail .task-chat-body img,
        .task-detail .task-chat-body video {
            max-width: 100%;
            height: auto;
        }

        .task-detail .task-chat-empty {
            margin: auto;
            padding: 30px 0;
            text-align: center;
            color: #8a97ab;
        }

        .task-detail .task-chat-empty i {
            display: block;
            font-size: 30px;
            margin-bottom: 6px;
        }

        /* Files tab */
        .task-detail .task-primary-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0d6efd;
            color: #fff;
            border: 0;
            border-radius: 8px;
            padding: 10px 22px;
            font-weight: 600;
            white-space: nowrap;
        }

        .task-detail .task-primary-btn:hover {
            background: #0955c7;
        }

        .task-detail .task-secondary-btn {
            background: #eef1f6;
            color: #46536b;
            border: 0;
            border-radius: 8px;
            padding: 10px 22px;
            font-weight: 600;
        }

        .task-detail .task-secondary-btn:hover {
            background: #dfe4ec;
        }

        .task-detail .task-file-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .task-detail .task-file-actions form {
            display: inline-flex;
            margin: 0;
        }

        /* small versions of the header tool buttons */
        .task-detail .task-tool-btn.is-sm {
            width: 36px;
            height: 36px;
            font-size: 16px;
        }

        .task-detail .task-tool-btn.is-view {
            background: #e7f0ff;
            color: #0d6efd;
        }

        .task-detail .task-tool-btn.is-view:hover {
            background: #0d6efd;
            color: #fff;
        }

        .task-detail .task-tool-btn.is-download {
            background: #dcfce7;
            color: #16a34a;
        }

        .task-detail .task-tool-btn.is-download:hover {
            background: #16a34a;
            color: #fff;
        }

        .task-detail .task-tool-btn.is-delete {
            background: #fde8e8;
            color: #dc2626;
        }

        .task-detail .task-tool-btn.is-delete:hover {
            background: #dc2626;
            color: #fff;
        }

        .task-detail .task-upload-panel {
            margin-top: 0;
            background: #fff;
            border: 1px solid #e6edf7;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }

        .task-detail .task-file-drop {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 2px dashed #c9d6ea;
            border-radius: 12px;
            background: #f8faff;
            padding: 28px 20px;
            text-align: center;
            color: #60708b;
            cursor: pointer;
            transition: border-color .15s ease, background-color .15s ease;
        }

        .task-detail .task-file-drop:hover,
        .task-detail .task-file-drop.is-dragover {
            border-color: #0d6efd;
            background: #eef4ff;
        }

        .task-detail .task-file-drop i {
            font-size: 30px;
            color: #0d6efd;
        }

        .task-detail .task-file-drop strong {
            color: #00001e;
        }

        .task-detail .task-selected-file {
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #e6edf7;
            border-radius: 8px;
            padding: 8px 12px;
            margin-top: 10px;
        }

        .task-detail .task-selected-file .file-size {
            color: #8a97ab;
            font-size: 13px;
            white-space: nowrap;
        }

        .task-detail .bottom_tab_section .nav-link .count {
            color: #8a97ab;
            font-weight: 500;
        }

        /* Task detail dashboard styling, scoped to this page. */
        .task-detail {
            --task-detail-line: #dfe8f4;
            --task-detail-muted: #667792;
            padding: 0 22px 36px;
            color: #15223d;
        }

        .task-detail .pm-crumb {
            margin: 0 0 16px;
            color: var(--task-detail-muted);
            font-size: 15px;
        }

        .task-detail .pm-project-head.track_timer {
            align-items: center;
            margin: 0;
            padding: 26px 28px;
            background: #fff;
            border: 1px solid var(--task-detail-line);
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(34, 73, 125, .045);
        }

        .task-detail .pm-project-head.track_timer .pm-project-icon {
            width: 72px;
            height: 72px;
            flex-basis: 72px;
            border-radius: 16px;
            background: #f1eaff;
            color: #8138f5;
            font-size: 1.8rem;
        }

        .task-detail .pm-project-head.track_timer > .left_action_btn {
            gap: 20px;
        }

        .task-detail .pm-project-head.track_timer > .left_action_btn h1 {
            color: #14213d;
            font-size: clamp(1.4rem, 2vw, 2rem);
            font-weight: 750;
            line-height: 1.25;
        }

        .task-detail .pm-project-head.track_timer .left_action_btn p,
        .task-detail .pm-project-head.track_timer > .right_action_btn > p.timer {
            color: var(--task-detail-muted);
            font-size: 1rem;
        }

        .task-detail .pm-project-head.track_timer > .right_action_btn {
            gap: 12px;
        }

        .task-detail .card_tracker {
            margin-top: 20px;
        }

        .task-detail .card_tracker .grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 18px;
        }

        .task-detail .card_tracker .card {
            justify-content: center;
            gap: 8px;
            min-height: 96px;
            padding: 14px 20px;
            border-color: var(--task-detail-line);
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(34, 73, 125, .035);
        }

        .task-detail .card_tracker .card-label {
            margin: 0;
            color: var(--task-detail-muted);
            font-size: 14px;
        }

        .task-detail .card_tracker .card-value {
            gap: 10px;
            color: #14213d;
            font-size: 16px;
            font-weight: 650;
        }

        .task-detail .card_tracker .avatar {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            font-size: 15px;
        }

        .task-detail .card_tracker .card-value > span {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .task-detail .task-due-date-history-card {
            margin: 22px 0 0;
            padding: 24px 28px;
            border-color: var(--task-detail-line);
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(34, 73, 125, .035);
        }

        .task-detail .task-due-date-history-card .story-summary-section {
            align-items: center;
            gap: 20px;
            margin: 0;
            padding: 0;
            border: 0;
        }

        .task-detail .task-due-date-history-card .story-summary-section-icon {
            width: 64px;
            height: 64px;
            flex: 0 0 64px;
            border-radius: 14px;
        }

        .task-detail .task-due-date-history-card .story-summary-section-body {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px 28px;
        }

        .task-detail .task-due-date-history-card .story-summary-section-body h2 {
            margin: 0;
            color: #14213d;
            font-size: 1.45rem;
            font-weight: 700;
        }

        .task-detail .task-due-date-history-card .story-date-timeline {
            gap: 14px;
        }

        .task-detail .bottom_tab_section {
            margin-top: 24px;
        }

        .task-detail .bottom_tab_section ul.nav {
            gap: 0;
            border-color: var(--task-detail-line);
        }

        .task-detail .bottom_tab_section .nav-link {
            padding: 12px 28px 14px;
            border-bottom-width: 3px;
            color: var(--task-detail-muted);
            font-size: 16px;
        }

        .task-detail .task-description {
            min-height: 120px;
            padding: 28px 30px;
            border-color: var(--task-detail-line);
            border-radius: 10px;
            color: #17243d;
            font-size: 17px;
            line-height: 1.65;
            box-shadow: 0 3px 12px rgba(34, 73, 125, .025);
        }

        @media (max-width: 1199.98px) {
            .task-detail .card_tracker .grid {
                grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .task-detail {
                padding-right: 14px;
                padding-left: 14px;
            }

            .task-detail .pm-project-head.track_timer {
                padding: 20px;
            }

            .task-detail .pm-project-head.track_timer .pm-project-icon {
                width: 54px;
                height: 54px;
                flex-basis: 54px;
                font-size: 1.4rem;
            }

            .task-detail .task-due-date-history-card {
                padding: 18px;
            }

            .task-detail .task-due-date-history-card .story-summary-section-icon {
                width: 52px;
                height: 52px;
                flex-basis: 52px;
            }

            .task-detail .task-due-date-history-card .story-summary-section-body {
                display: block;
            }

            .task-detail .task-due-date-history-card .story-date-timeline {
                margin-top: 12px;
                flex-wrap: wrap;
            }

            .task-detail .bottom_tab_section .nav-link {
                padding-right: 16px;
                padding-left: 16px;
            }

            .task-detail .task-description {
                padding: 20px;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $canUpdate = Auth::user()->can('tasks-update', $task);
        $canControlTimer = Auth::user()->hasRole(\App\Support\Roles::ADMIN) || $task->user_id === Auth::id();
        $canTrackManually = Auth::user()->can('tasks-track-manually', $task) && $canControlTimer;
        $canEditTime = Auth::user()->can('tasks-time-update');
    @endphp

    <div class="container-fluid mt-80 task-detail">
        @if ($task->deliveryProject || $task->story)
            <div class="pm-crumb">
                <a href="{{ route('projects.index') }}">Projects</a>
                <i class="bi bi-chevron-right"></i>
                <a href="{{ route('projects.show', $task->deliveryProject) }}" title="{{ $task->deliveryProject->name }}">{{ \Illuminate\Support\Str::words($task->deliveryProject->name, 3, '…') }}</a>
                <i class="bi bi-chevron-right"></i>
                <a href="{{ route('stories.show', $task->story) }}" title="{{ $task->story->title }}">{{ \Illuminate\Support\Str::words($task->story->title, 3, '…') }}</a>
                <i class="bi bi-chevron-right"></i>
                <span title="{{ $task->title }}">{{ \Illuminate\Support\Str::words($task->title, 3, '…') }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="alert alert-warning alert-dismissible fade show" role="alert" id="jsresp" style="display: none">
            <span id="textpara"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        {{-- Title, status and time tracker --}}
        <div class="pm-project-head track_timer">
            <div class="left_action_btn">
                <div class="pm-project-icon">
                    <i class="bi bi-kanban"></i>
                </div>
                <div>
                    <h1 class="task-title-row">
                        <span class="task-title-text">{{ $task->title }}</span>
                        @php
                            $statusOptions = collect(config('site.tasks.status'))->except('closed');
                            $currentStatusLabel = config('site.tasks.status')[$task->status] ?? ucfirst(str_replace('_', ' ', $task->status));
                        @endphp
                        @if ($canUpdate)
                            <div class="btn-group task-status-dropdown">
                                <button type="button" id="taskStatusToggle" class="task-status task-status-toggle task-status-{{ $task->status }}" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Change task status">
                                    <span id="taskStatusLabel">{{ $currentStatusLabel }}</span>
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <ul class="dropdown-menu task-status-menu shadow-sm">
                                    @foreach ($statusOptions as $value => $label)
                                        <li>
                                            <a href="#" class="dropdown-item task-status-option {{ $task->status === $value ? 'active' : '' }}" data-status="{{ $value }}" data-label="{{ $label }}">
                                                <span class="task-status-dot task-status-dot-{{ $value }}"></span>{{ $label }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <span class="pm-badge task-status task-status-{{ $task->status }}">{{ $currentStatusLabel }}</span>
                        @endif
                    </h1>
                    <p>
                        {{ $task->project?->name ?? 'No client' }}
                        @if ($task->created_at)
                            &middot; Created {{ $task->created_at->format('d M Y') }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="right_action_btn">
                @if(!Auth::user()->hasRole(\App\Support\Roles::CLIENT))
                    <p class="timer">Time Tracker ( <span id="time-display">00:00:00</span> )</p>
                @endif
                <div class="right_action_btn">
                    @if(!Auth::user()->hasRole(\App\Support\Roles::CLIENT))
                        @if ($canControlTimer)
                            <button type="button" class="start_btn" id="start-btn" @if ($task->is_running) style="display: none" @endif>
                                <i class="bi bi-play-circle"></i> Start Timer <span><i class="bi bi-arrow-right"></i></span>
                            </button>
                            <button type="button" class="stop_btn" id="stop-btn" @unless ($task->is_running) style="display: none" @endunless>
                                <i class="bi bi-stop-circle"></i> Stop Timer <span><i class="bi bi-arrow-right"></i></span>
                            </button>
                        @endif
                    @endif
                    @if ($canTrackManually)
                        <button type="button" class="track_btn" data-bs-toggle="modal" data-bs-target="#manualtrack">Track Manually</button>
                    @endif
                    <div class="task-head-tools">
                        @if ($canUpdate)
                            <button type="button" class="task-tool-btn is-edit" data-bs-toggle="modal"
                                data-bs-target="#editTaskModal" title="Edit task">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        @endif
                        <a href="{{ $task->story_id ? route('stories.show', $task->story_id) : route('tasks.index', $task->project ? ['project_id' => $task->project->id] : []) }}"
                            class="task-tool-btn is-back" title="{{ $task->story_id ? 'Back to story' : 'Back to tasks' }}">
                            <i class="bi bi-arrow-90deg-left"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="card_tracker">
            <div class="grid">
                <div class="card">
                    <span class="card-label">Assigned To</span>
                    <div class="card-value">
                        <div class="avatar">{{ strtoupper(mb_substr($task->user?->name ?? '?', 0, 1)) }}</div>
                        <span>{{ $task->user?->name ?? 'Unassigned' }}</span>
                    </div>
                </div>

                <div class="card">
                    <span class="card-label">Due Date</span>
                    <div class="card-value">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <span>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d F Y') : 'Not set' }}</span>
                    </div>
                </div>

                <div class="card">
                    <span class="card-label">Priority</span>
                    <div class="card-value">
                        <span class="pm-badge priority-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span>
                    </div>
                </div>

                <div class="card">
                    <span class="card-label">Estimated Hours</span>
                    <div class="card-value">
                        <svg class="icon-green" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>{{ $task->estimated_hours !== null ? rtrim(rtrim(number_format((float) $task->estimated_hours, 2), '0'), '.') . ' hrs' : 'Not set' }}</span>
                    </div>
                </div>

                @if(!Auth::user()->hasRole(\App\Support\Roles::CLIENT))
                <div class="card">
                    <span class="card-label">Time Spent</span>
                    <div class="card-value">
                        <svg class="icon-blue" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span id="tracked_hours">{{ $totalTime }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if ($task->dueDateHistories->isNotEmpty())
            @php
                $taskDueDateTimeline = collect();
                $firstTaskDueDateChange = $task->dueDateHistories->first();
                $formatTaskDueDate = fn ($date) => strtolower($date->format('d-M-Y'));

                if ($firstTaskDueDateChange->old_due_date) {
                    $taskDueDateTimeline->push($formatTaskDueDate($firstTaskDueDateChange->old_due_date));
                }

                foreach ($task->dueDateHistories as $dueDateChange) {
                    $taskDueDateTimeline->push(
                        $dueDateChange->new_due_date
                            ? $formatTaskDueDate($dueDateChange->new_due_date)
                            : 'No due date'
                    );
                }
            @endphp
            <div class="pm-card task-due-date-history-card">
                <section class="story-summary-section story-due-date-section">
                    <div class="story-summary-section-icon due-date-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="story-summary-section-body">
                        <h2>Due Date History</h2>
                        <div class="story-date-timeline">
                            @foreach ($taskDueDateTimeline as $dateIndex => $dateLabel)
                                @if ($dateIndex > 0)
                                    <i class="bi bi-arrow-right story-date-arrow" aria-hidden="true"></i>
                                @endif
                                <span class="story-date-pill {{ $loop->last ? 'current' : '' }}">{{ $dateLabel }}</span>
                            @endforeach
                            <span class="story-date-updated"><i></i> Updated</span>
                        </div>
                    </div>
                </section>
            </div>
        @endif

        {{-- Tabs --}}
        <div class="bottom_tab_section">
            <ul class="nav nav-pills mb-3" id="task-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-description-btn" data-bs-toggle="pill"
                        data-bs-target="#tab-description" type="button" role="tab" aria-controls="tab-description"
                        aria-selected="true">Description</button>
                </li>
                @if(!Auth::user()->hasRole(\App\Support\Roles::CLIENT))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-time-log-btn" data-bs-toggle="pill" data-bs-target="#tab-time-log"
                        type="button" role="tab" aria-controls="tab-time-log" aria-selected="false">
                        Time Log <span class="count">({{ $taskHistories->count() }})</span>
                    </button>
                </li>
                @endif
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-files-btn" data-bs-toggle="pill" data-bs-target="#tab-files"
                        type="button" role="tab" aria-controls="tab-files" aria-selected="false">
                        Files <span class="count">({{ $files->count() }})</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-chat-btn" data-bs-toggle="pill" data-bs-target="#tab-chat"
                        type="button" role="tab" aria-controls="tab-chat" aria-selected="false">
                        Chat <span class="count">({{ $chats->count() }})</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="task-tabs-content">
                {{-- Description --}}
                <div class="tab-pane fade show active" id="tab-description" role="tabpanel"
                    aria-labelledby="tab-description-btn">
                    <div class="task-description">
                        @if (filled(trim(strip_tags((string) $task->description))))
                            {!! $task->description !!}
                        @else
                            <span class="text-muted">No description has been added.</span>
                        @endif
                    </div>
                </div>

                {{-- Time log --}}
                <div class="tab-pane fade" id="tab-time-log" role="tabpanel" aria-labelledby="tab-time-log-btn">
                    <div class="table-responsive project_table">
                        <table class="table pm-table mb-0 align-middle">
                            <tr>
                                <th>No</th>
                                <th>Start time</th>
                                <th>End Time</th>
                                <th>Total Time</th>
                            </tr>
                            @forelse ($taskHistories as $taskHistory)
                                <tr>
                                    <td data-id="{{ $taskHistory->id }}">{{ $loop->iteration }}</td>
                                    <td data-id="{{ $taskHistory->id }}">
                                        <span class="start-time-text">
                                            {{ $taskHistory->start_time }}
                                        </span>
                                        @if ($canEditTime)
                                            <div class="align-items-center d-flex gap-2 mt-1 time-edit d-none">
                                                <div>
                                                    <input type="datetime-local"
                                                        class="form-control form-control-sm start-time-input"
                                                        value="{{ \Carbon\Carbon::parse($taskHistory->start_time)->format('Y-m-d\TH:i') }}">
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td data-id="{{ $taskHistory->id }}">
                                        <span class="end-time-text">
                                            {{ $taskHistory->end_time }}
                                        </span>
                                        @if ($canEditTime)
                                            <div class="align-items-center d-flex gap-2 mt-1 time-edit d-none">
                                                <div>
                                                    <input type="datetime-local"
                                                        class="form-control form-control-sm end-time-input"
                                                        value="{{ $taskHistory->end_time ? \Carbon\Carbon::parse($taskHistory->end_time)->format('Y-m-d\TH:i') : '' }}">
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td data-id="{{ $taskHistory->id }}">
                                        <span class="time-text">
                                            {{ $taskHistory->elapsed_time_formatted }}
                                        </span>
                                        @if ($canEditTime)
                                            <button class="btn btn-sm btn-primary edit-time ms-2">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <div class="align-items-center d-flex gap-2 mt-1 time-edit d-none">
                                                <div>
                                                    <input type="text" class="form-control form-control-sm time-input"
                                                        value="{{ $taskHistory->elapsed_time_formatted }}"
                                                        placeholder="HH:MM:SS">
                                                </div>

                                                <div class="mt-1">
                                                    <button class="btn btn-success btn-sm save-time">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                    <button class="btn btn-secondary btn-sm cancel-time">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No time has been tracked on this task yet.</td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </div>

                {{-- Files --}}
                <div class="tab-pane fade" id="tab-files" role="tabpanel" aria-labelledby="tab-files-btn">
                    <div class="alert alert-success d-none" id="taskFilesAlert"></div>

                    @if ($canUpdate)
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" class="task-primary-btn" data-bs-toggle="collapse"
                                data-bs-target="#uploadFilesPanel" aria-expanded="{{ $errors->files->any() ? 'true' : 'false' }}"
                                aria-controls="uploadFilesPanel">
                                <i class="bi bi-upload"></i> Upload Files
                            </button>
                        </div>

                        <div class="collapse {{ $errors->files->any() ? 'show' : '' }}" id="uploadFilesPanel">
                            <form class="task-upload-panel" action="{{ route('tasks.files.store', $task->id) }}" method="POST"
                                enctype="multipart/form-data" id="uploadFilesForm">
                                @csrf

                                @if ($errors->files->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach (array_unique($errors->files->all()) as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="alert alert-danger d-none" id="uploadFilesErrors"></div>

                                <label class="task-file-drop" for="task-files-input" id="taskFileDrop">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <strong>Click to choose files, or drop them here</strong>
                                    <span>You can select several files at once</span>
                                </label>
                                <input type="file" name="files[]" id="task-files-input" class="d-none" multiple>

                                <div id="taskSelectedFiles"></div>

                                <div class="progress mt-3 d-none" id="uploadFilesProgress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <button type="button" class="task-secondary-btn" data-bs-toggle="collapse"
                                        data-bs-target="#uploadFilesPanel">Cancel</button>
                                    <button type="submit" class="task-primary-btn" id="uploadFilesSubmit" disabled>
                                        <i class="bi bi-upload"></i> <span>Upload</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <div class="table-responsive project_table">
                        <table class="table pm-table mb-0 align-middle">
                            <tbody>
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Uploaded</th>
                                    <th>Action</th>
                                </tr>
                            </tbody>
                            <tbody id="taskFilesBody">
                                @include('tasks.files.rows', ['files' => $files, 'task' => $task])
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Chat --}}
                <div class="tab-pane fade" id="tab-chat" role="tabpanel" aria-labelledby="tab-chat-btn">
                    <div class="task-chat-card">
                    <div class="task-chat-list" id="taskChatList">
                        @forelse ($chats as $chat)
                            @include('tasks.chat-message', ['chat' => $chat])
                        @empty
                            <div class="task-chat-empty" id="taskChatEmpty">
                                <i class="bi bi-chat-dots"></i>
                                No messages yet. Start the conversation below.
                            </div>
                        @endforelse
                    </div>

                    <form id="chatForm" class="task-chat-composer" method="POST"
                        action="{{ route('tasks.chat.store', $task->id) }}">
                        @csrf
                        <div class="alert alert-danger d-none" id="chatError"></div>
                        <div class="task-chat-box">
                            <div class="task-chat-toolbar">
                                <button type="button" data-chat-format="bold" title="Bold (Ctrl+B)"><i class="bi bi-type-bold"></i></button>
                                <button type="button" data-chat-format="italic" title="Italic (Ctrl+I)"><i class="bi bi-type-italic"></i></button>
                                <button type="button" data-chat-format="underline" title="Underline (Ctrl+U)"><i class="bi bi-type-underline"></i></button>
                                <button type="button" data-chat-format="list" title="Bullet list"><i class="bi bi-list-ul"></i></button>
                                <button type="button" data-chat-format="numbered" title="Numbered list"><i class="bi bi-list-ol"></i></button>
                            </div>
                            <textarea id="chatMessage" name="message" maxlength="5000"
                                placeholder="Write a message…"></textarea>
                        </div>
                        <div class="task-chat-actions">
                            <span class="task-chat-hint">**bold** &nbsp; *italic* &nbsp; __underline__ &nbsp; - list &nbsp;&middot;&nbsp; Ctrl+Enter to send</span>
                            <button class="task-primary-btn" type="submit" id="chatSend">
                                <i class="bi bi-send"></i> <span>Send</span>
                            </button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit task --}}
        @if ($canUpdate)
            <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" name="title" id="title" class="form-control"
                                        value="{{ $task->title }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" id="summernote" class="form-control">{{ $task->description }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" name="due_date" id="due_date" class="form-control"
                                        value="{{ $task->due_date }}">
                                </div>
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select name="priority" id="priority" class="form-select" required>
                                        <option value="low" {{ $task->priority == 'low' ? 'selected' : '' }}>Low
                                        </option>
                                        <option value="medium" {{ $task->priority == 'medium' ? 'selected' : '' }}>Medium
                                        </option>
                                        <option value="high" {{ $task->priority == 'high' ? 'selected' : '' }}>High
                                        </option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select" required>
                                        @foreach (config('site.tasks.status') as $key => $value)
                                            @unless (in_array($key, $hiddenStatuses))
                                                <option value="{{ $key }}" @selected($task->status == $key)>
                                                    {{ $value }}
                                                </option>
                                            @endunless
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Assign To</label>
                                    <select name="user_id" id="user_id" class="form-select" required>
                                        <option value="">Select a user</option>
                                        @foreach ($task->deliveryProject?->members ?? [] as $user)
                                            <option value="{{ $user->id }}" @selected($user->id == $task->user_id)>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Update Task</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Track time manually --}}
        @if ($canTrackManually)
            <div class="modal fade" id="manualtrack" tabindex="-1" aria-labelledby="manualtrackLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('tasks.manual-time.update', $task->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title" id="manualtrackLabel">Track Time Manually</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="start" class="form-label">Start Time</label>
                                    <input type="datetime-local" name="start" id="start" class="form-control"
                                        value="{{ old('start') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="end" class="form-label">End Time</label>
                                    <input type="datetime-local" name="end" id="end" class="form-control"
                                        value="{{ old('end') }}" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Add Time</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>

    @push('scripts')
        <script>
            (function () {
                var toggle = document.getElementById('taskStatusToggle');
                if (!toggle) return;
                var label = document.getElementById('taskStatusLabel');
                var options = document.querySelectorAll('.task-status-option');
                var url = @json(route('tasks.status.update', $task));
                var token = @json(csrf_token());
                var current = @json($task->status);

                options.forEach(function (option) {
                    option.addEventListener('click', function (e) {
                        e.preventDefault();
                        var status = option.dataset.status;
                        if (status === current) return;
                        var previous = current;
                        var body = new FormData();
                        body.append('status', status);
                        toggle.classList.add('is-saving');

                        fetch(url, {
                            method: 'POST',
                            headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                            body: body
                        }).then(function (res) {
                            if (!res.ok) throw new Error('Request failed');
                            toggle.classList.remove('task-status-' + previous);
                            toggle.classList.add('task-status-' + status);
                            label.textContent = option.dataset.label;
                            options.forEach(function (o) { o.classList.toggle('active', o === option); });
                            current = status;
                        }).catch(function () {
                            alert('Could not update the task status. Please try again.');
                        }).finally(function () {
                            toggle.classList.remove('is-saving');
                        });
                    });
                });
            })();
        </script>
    @endpush

    @push('scripts')
        <script>
            // Open the tab named in the URL (#tab-files) and keep the URL in step with the open tab
            (function() {
                const hash = /^#tab-[a-z-]+$/.test(window.location.hash) ? window.location.hash : '';
                const hashTab = hash ? document.querySelector('[data-bs-target="' + hash + '"]') : null;
                if (hashTab) {
                    bootstrap.Tab.getOrCreateInstance(hashTab).show();
                }
                document.querySelectorAll('#task-tabs [data-bs-toggle="pill"]').forEach(function(button) {
                    button.addEventListener('shown.bs.tab', function() {
                        history.replaceState(null, '', button.dataset.bsTarget);
                    });
                });
            })();

            // Upload panel: list the chosen files with an editable name each
            (function() {
                const input = document.getElementById('task-files-input');
                if (!input) {
                    return;
                }
                const drop = document.getElementById('taskFileDrop');
                const list = document.getElementById('taskSelectedFiles');
                const submit = document.getElementById('uploadFilesSubmit');

                function formatSize(bytes) {
                    if (bytes >= 1048576) {
                        return (bytes / 1048576).toFixed(1) + ' MB';
                    }
                    return Math.max(1, Math.round(bytes / 1024)) + ' KB';
                }

                function render() {
                    list.innerHTML = '';
                    Array.from(input.files).forEach(function(file) {
                        const row = document.createElement('div');
                        row.className = 'task-selected-file';

                        const icon = document.createElement('i');
                        icon.className = 'bi bi-file-earmark';

                        const name = document.createElement('input');
                        name.type = 'text';
                        name.name = 'names[]';
                        name.className = 'form-control form-control-sm';
                        name.maxLength = 255;
                        name.title = 'Name shown for this file';
                        name.value = file.name.replace(/\.[^.]+$/, '');

                        const size = document.createElement('span');
                        size.className = 'file-size';
                        size.textContent = file.name.split('.').pop().toUpperCase() + ' · ' + formatSize(file.size);

                        row.append(icon, name, size);
                        list.appendChild(row);
                    });
                    const count = input.files.length;
                    submit.disabled = count === 0;
                    submit.querySelector('span').textContent = count > 1 ? 'Upload ' + count + ' files' : 'Upload';
                }

                input.addEventListener('change', render);

                ['dragenter', 'dragover'].forEach(function(type) {
                    drop.addEventListener(type, function(event) {
                        event.preventDefault();
                        drop.classList.add('is-dragover');
                    });
                });
                ['dragleave', 'drop'].forEach(function(type) {
                    drop.addEventListener(type, function(event) {
                        event.preventDefault();
                        drop.classList.remove('is-dragover');
                    });
                });
                drop.addEventListener('drop', function(event) {
                    if (event.dataTransfer && event.dataTransfer.files.length) {
                        input.files = event.dataTransfer.files;
                        render();
                    }
                });

                document.getElementById('uploadFilesPanel').addEventListener('hidden.bs.collapse', function() {
                    input.value = '';
                    render();
                    showErrors([]);
                });

                // Upload in the background, then refresh the list in place
                const form = document.getElementById('uploadFilesForm');
                const errorBox = document.getElementById('uploadFilesErrors');
                const successBox = document.getElementById('taskFilesAlert');
                const progress = document.getElementById('uploadFilesProgress');
                const progressBar = progress.querySelector('.progress-bar');
                const submitLabel = submit.querySelector('span');

                function showErrors(messages) {
                    errorBox.innerHTML = '';
                    errorBox.classList.toggle('d-none', messages.length === 0);
                    if (messages.length) {
                        const list = document.createElement('ul');
                        list.className = 'mb-0';
                        messages.forEach(function(message) {
                            const item = document.createElement('li');
                            item.textContent = message;
                            list.appendChild(item);
                        });
                        errorBox.appendChild(list);
                    }
                }

                function setBusy(busy) {
                    submit.disabled = busy || input.files.length === 0;
                    form.querySelectorAll('button, input').forEach(function(el) {
                        if (el !== submit && el.type !== 'hidden') {
                            el.disabled = busy;
                        }
                    });
                    drop.style.pointerEvents = busy ? 'none' : '';
                    progress.classList.toggle('d-none', !busy);
                    if (!busy) {
                        progressBar.style.width = '0%';
                    }
                }

                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    if (!input.files.length) {
                        return;
                    }
                    showErrors([]);
                    successBox.classList.add('d-none');

                    // collect the fields before they are disabled, or they would be left out
                    const data = new FormData(form);
                    const label = submitLabel.textContent;
                    setBusy(true);
                    submitLabel.textContent = 'Uploading…';

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', form.action);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            progressBar.style.width = Math.round((e.loaded / e.total) * 100) + '%';
                        }
                    });
                    xhr.addEventListener('load', function() {
                        let body = {};
                        try {
                            body = JSON.parse(xhr.responseText);
                        } catch (e) {
                            body = {};
                        }
                        setBusy(false);
                        submitLabel.textContent = label;

                        if (xhr.status >= 200 && xhr.status < 300 && body.rows !== undefined) {
                            document.getElementById('taskFilesBody').innerHTML = body.rows;
                            document.querySelector('#tab-files-btn .count').textContent = '(' + body.count + ')';
                            successBox.textContent = body.message;
                            successBox.classList.remove('d-none');
                            bootstrap.Collapse.getOrCreateInstance(document.getElementById('uploadFilesPanel')).hide();
                            return;
                        }
                        if (xhr.status === 422 && body.errors) {
                            showErrors(body.errors);
                        } else if (xhr.status === 413) {
                            showErrors(['The upload is larger than the server allows. Try fewer or smaller files.']);
                        } else if (xhr.status === 419) {
                            showErrors(['Your session has expired. Reload the page and try again.']);
                        } else if (xhr.status === 403) {
                            showErrors(['You are not allowed to upload files to this task.']);
                        } else {
                            showErrors(['The upload failed. Please try again.']);
                        }
                    });
                    xhr.addEventListener('error', function() {
                        setBusy(false);
                        submitLabel.textContent = label;
                        showErrors(['The upload failed. Check your connection and try again.']);
                    });
                    xhr.send(data);
                });
            })();

            // Chat: send in the background and add the message to the list
            (function() {
                const form = document.getElementById('chatForm');
                const list = document.getElementById('taskChatList');
                const errorBox = document.getElementById('chatError');
                const send = document.getElementById('chatSend');
                const sendLabel = send.querySelector('span');
                const box = document.getElementById('chatMessage');

                // grow with the text, up to the height set in the styles
                function fitBox() {
                    box.style.height = 'auto';
                    box.style.height = Math.max(74, box.scrollHeight) + 'px';
                }
                box.addEventListener('input', fitBox);

                // wrap the selection in markers, or start list lines
                function applyFormat(format) {
                    const start = box.selectionStart;
                    const end = box.selectionEnd;
                    const selected = box.value.slice(start, end);
                    let insert;
                    let selectFrom;
                    let selectTo;

                    if (format === 'list' || format === 'numbered') {
                        const lines = (selected || 'List item').split('\n');
                        insert = lines.map(function(line, index) {
                            return (format === 'list' ? '- ' : (index + 1) + '. ') + line;
                        }).join('\n');
                        if (start > 0 && box.value[start - 1] !== '\n') {
                            insert = '\n' + insert;
                        }
                        selectFrom = start + insert.length;
                        selectTo = selectFrom;
                    } else {
                        const marker = { bold: '**', italic: '*', underline: '__' }[format];
                        const text = selected || 'text';
                        insert = marker + text + marker;
                        selectFrom = start + marker.length;
                        selectTo = selectFrom + text.length;
                    }

                    box.setRangeText(insert, start, end, 'end');
                    box.focus();
                    box.setSelectionRange(selectFrom, selectTo);
                    fitBox();
                }

                form.querySelectorAll('[data-chat-format]').forEach(function(button) {
                    button.addEventListener('click', function() {
                        applyFormat(button.dataset.chatFormat);
                    });
                });

                box.addEventListener('keydown', function(event) {
                    if (!(event.ctrlKey || event.metaKey)) {
                        return;
                    }
                    const format = { b: 'bold', i: 'italic', u: 'underline' }[event.key.toLowerCase()];
                    if (format) {
                        event.preventDefault();
                        applyFormat(format);
                    } else if (event.key === 'Enter') {
                        event.preventDefault();
                        form.requestSubmit();
                    }
                });

                function scrollToLatest() {
                    list.scrollTop = list.scrollHeight;
                }

                function showError(message) {
                    errorBox.textContent = message || '';
                    errorBox.classList.toggle('d-none', !message);
                }

                // the list has no height while its tab is hidden, so scroll when the tab opens
                document.getElementById('tab-chat-btn').addEventListener('shown.bs.tab', scrollToLatest);
                scrollToLatest();

                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    if (send.disabled) {
                        return;
                    }
                    showError('');
                    send.disabled = true;
                    sendLabel.textContent = 'Sending…';

                    $.ajax({
                        url: form.action,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            message: box.value,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            const empty = document.getElementById('taskChatEmpty');
                            if (empty) {
                                empty.remove();
                            }
                            list.insertAdjacentHTML('beforeend', response.message);
                            document.querySelector('#tab-chat-btn .count').textContent = '(' + response.count + ')';
                            box.value = '';
                            fitBox();
                            box.focus();
                            scrollToLatest();
                        },
                        error: function(xhr) {
                            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                                showError(xhr.responseJSON.errors.join(' '));
                            } else if (xhr.status === 419) {
                                showError('Your session has expired. Reload the page and try again.');
                            } else if (xhr.status === 403) {
                                showError('You are not allowed to send messages on this task.');
                            } else {
                                showError('The message could not be sent. Please try again.');
                            }
                        },
                        complete: function() {
                            send.disabled = false;
                            sendLabel.textContent = 'Send';
                        }
                    });
                });
            })();

            let timer;
            let seconds = {{ (int) $task->elapsed_time }};
            let isRunning = {{ $task->is_running ? 'true' : 'false' }};

            function formatTime(sec) {
                let hours = Math.floor(sec / 3600);
                let minutes = Math.floor((sec % 3600) / 60);
                let seconds = sec % 60;
                return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }

            function updateTimeDisplay() {
                $('#time-display').text(formatTime(seconds));
            }

            function saveTime() {
                $.ajax({
                    url: '{{ route('tasks.timer.save', ['task' => $task->id]) }}',
                    method: 'POST',
                    data: {
                        elapsed_time: seconds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        seconds = response.elapsed_time;
                    },
                    error: function(error) {
                        console.error('Error saving time:', error);
                    }
                });
            }

            function startTicking() {
                timer = setInterval(() => {
                    seconds++;
                    updateTimeDisplay();
                    if (seconds % 10 === 0) {
                        saveTime();
                    }
                }, 1000);
            }

            // Only one of the two buttons is ever visible
            function showTimerButton(running) {
                $('#start-btn').toggle(!running);
                $('#stop-btn').toggle(running);
            }

            if (isRunning) {
                showTimerButton(true);
                startTicking();
            }

            // Disable the button and show a "Please wait..." label; returns a function that restores it
            function setButtonLoading(button) {
                const originalHtml = button.html();
                button.prop('disabled', true).html(
                    '<div style="display: flex; align-items: center; gap: 8px; padding-right: 20px;">' +
                    '<div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>' +
                    '<div>Please wait ...</div></div>'
                );

                return function() {
                    button.prop('disabled', false).html(originalHtml);
                };
            }

            $('#start-btn').on('click', function() {
                if (isRunning || $(this).prop('disabled')) {
                    return;
                }
                const button = $(this);
                const restoreButton = setButtonLoading(button);

                $.ajax({
                    url: '{{ route('tasks.timer.start', ['task' => $task->id]) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status == false) {
                            isRunning = false;
                            $('#textpara').text(response.message);
                            $('#jsresp').show();
                        } else {
                            isRunning = true;
                            startTicking();
                            showTimerButton(true);
                        }
                    },
                    error: function(error) {
                        console.error('Error starting timer:', error);
                    },
                    complete: restoreButton
                });
            });

            $('#stop-btn').on('click', function() {
                if ($(this).prop('disabled')) {
                    return;
                }
                const button = $(this);
                const restoreButton = setButtonLoading(button);

                $.ajax({
                    url: '{{ route('tasks.timer.stop', ['task' => $task->id]) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#tracked_hours').text(response.total_time);
                        showTimerButton(false);

                        isRunning = false;
                        clearInterval(timer);
                        seconds = 0;
                        updateTimeDisplay();
                    },
                    error: function(error) {
                        console.error('Error stoping timer:', error);
                    },
                    complete: restoreButton
                });
            });

            updateTimeDisplay();

            // ------------------inline edit of time log entries
            // Convert HH:MM:SS → seconds
            function timeToSeconds(time) {
                let parts = time.split(':').map(p => parseInt(p) || 0);

                let h = 0,
                    m = 0,
                    s = 0;

                if (parts.length === 3) {
                    [h, m, s] = parts;
                } else if (parts.length === 2) {
                    [h, m] = parts; // treat as HH:MM
                } else if (parts.length === 1) {
                    h = parts[0];
                }

                return (h * 3600) + (m * 60) + s;
            }

            // Convert seconds → HH:MM:SS
            function secondsToTime(sec) {
                let h = Math.floor(sec / 3600);
                let m = Math.floor((sec % 3600) / 60);
                let s = sec % 60;

                return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            }

            // Edit Click
            $(document).on('click', '.edit-time', function() {
                let row = $(this).closest('tr');

                row.find('.time-text, .start-time-text, .end-time-text, .edit-time').hide();
                row.find('.time-edit').removeClass('d-none');
            });

            // Cancel Click
            $(document).on('click', '.cancel-time', function() {
                let row = $(this).closest('tr');

                row.find('.time-edit').addClass('d-none');
                row.find('.time-text, .start-time-text, .end-time-text, .edit-time').show();
            });

            // Save Click
            $(document).on('click', '.save-time', function() {
                let td = $(this).closest('td');
                let row = td.closest('tr');
                let id = td.data('id');
                let inputVal = td.find('.time-input').val();
                let startVal = row.find('.start-time-input').val();
                let endVal = row.find('.end-time-input').val();

                let tracker_seconds = timeToSeconds(inputVal);

                let url = "{{ route('tasks.time-entries.update', ':id') }}";
                url = url.replace(':id', id);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        elapsed_time: tracker_seconds,
                        start_time: startVal,
                        end_time: endVal,
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT'
                    },
                    success: function(res) {
                        if (res.success) {
                            td.find('.time-text').text(secondsToTime(tracker_seconds));
                            row.find('.start-time-text').text(startVal ? startVal.replace('T', ' ') : '');
                            row.find('.end-time-text').text(endVal ? endVal.replace('T', ' ') : '');

                            row.find('.time-edit').addClass('d-none');
                            row.find('.time-text, .start-time-text, .end-time-text, .edit-time').show();
                        }
                    },
                    error: function(error) {
                        console.log('Something went wrong', error);
                    }
                });
            });
        </script>
    @endpush
@endsection
