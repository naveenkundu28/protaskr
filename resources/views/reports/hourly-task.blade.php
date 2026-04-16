@extends('layouts.app')
@section('title', 'Hourly Report')
@section('content')

    <div class="container-fluid">
        <h2>Hourly Report</h2>

        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="row mb-3">

            <form method="GET" class="col-md-3" action="{{ route('daily.report') }}">
                <div class="input-group">
                    <input type="month" name="month" class="form-control" value="{{ request('month') ?? date('Y-m') }}">
                    <button class="btn btn-primary">Search</button>
                </div>
            </form>

            <div class="col-md-3">
                <select class="form-control" id="userFilter">
                    <option value="">All Users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>


            @if (auth()->user()?->hasRole('Admin'))
                <div class="col-md-3">
                    <form method="POST" class="input-group" action="{{ route('daily.report.generate-weekly') }}">

                        @csrf

                        <input type="date" name="report_date" class="form-control" max="{{ now()->format('Y-m-d') }}"
                            value="{{ now()->format('Y-m-d') }}">

                        <button type="submit" class="btn btn-info text-white">
                            <i class="bi bi-arrow-repeat me-1"></i> Fetch
                        </button>
                    </form>

                </div>
            @endif
        </div>

        <div class="table-scroll-top mb-1">
            <div></div>
        </div>

        <div class="table-responsive table-scroll-bottom">

            <table class="table table-bordered table-sm text-center text-nowrap report-table">
                <thead>
                    <tr class="f-12">
                        <th>#</th>
                        @foreach ($users as $key => $user)
                            <th data-user="{{ $user->id }}">
                                {{ $key + 1 }}
                            </th>
                        @endforeach
                    </tr>
                    <tr class="f-12">
                        <th>Date</th>
                        @foreach ($users as $user)
                            @php
                                $name = explode(' ', $user->name);
                                $initials = strtoupper(substr($name[0], 0, 1) . '_' . substr(end($name), 0, 1));
                            @endphp
                            <th class="px-4" data-user="{{ $user->id }}" title="{{ $user->name }}">
                                {{ $initials }}
                            </th>
                        @endforeach
                    </tr>
                    <tr class="f-12">
                        <th>Total</th>
                        @foreach ($users as $user)
                            <th data-user="{{ $user->id }}">
                                @php
                                    $minutes = $totals[$user->id] ?? 0;
                                @endphp
                                {{ sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60) }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dates as $date)
                        <tr class="f-12">
                            <td>{{ date('d (D)', strtotime($date)) }}</td>
                            @foreach ($users as $user)
                                @php
                                    $report = $reports[$date][$user->id][0] ?? null;
                                    $minutes = $report->time ?? 0;
                                @endphp
                                <td data-user="{{ $user->id }}" class="daily-report-cell p-0"
                                    title="{{ $report && $report->performance ? $user->name . ' - ' . $report->performance->title : $user->name . ' - Absent' }}"
                                    style="background-color: {{ $report->performance->background_color ?? '#8b1c1c' }}; color: {{ $report->performance->font_color ?? '#fff' }};">
                                    <button type="button"
                                        class="btn btn-link daily-report-trigger text-decoration-none p-0 border-0 w-100 h-100"
                                        @if (auth()->user()?->hasRole('Admin')) data-bs-toggle="modal"
                                        data-bs-target="#dailyReportConfirmModal"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        data-date="{{ $date }}"
                                        data-hours="{{ floor($minutes / 60) }}"
                                        data-minutes="{{ $minutes % 60 }}"
                                    @else
                                        disabled @endif
                                        style="color: inherit;">
                                        {{ sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60) }}
                                    </button>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr class="f-12">
                        <th>Total</th>
                        @foreach ($users as $user)
                            <th data-user="{{ $user->id }}">
                                @php
                                    $minutes = $totals[$user->id] ?? 0;
                                @endphp
                                {{ sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60) }}
                            </th>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>

        <h5 class="mt-4">Performance Legend</h5>
        <div class="d-flex flex-wrap gap-3 pb-4">
            @foreach ($performances as $p)
                <div
                    style="background: {{ $p->background_color }}; color: {{ $p->font_color }}; padding:6px 12px; border-radius:4px;">
                    {{ $p->title }}
                </div>
            @endforeach
        </div>

    </div>

    @if (auth()->user()?->hasRole('Admin'))
        <div class="modal fade" id="dailyReportConfirmModal" tabindex="-1" aria-labelledby="dailyReportConfirmModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dailyReportConfirmModalLabel">Edit Daily Hours</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        Do you want to edit this record for <strong id="dailyReportConfirmUserName"></strong> on <strong
                            id="dailyReportConfirmDate"></strong>?
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                        <button type="button" class="btn btn-primary" id="dailyReportConfirmYes">Yes</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="dailyReportEditModal" tabindex="-1" aria-labelledby="dailyReportEditModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('daily.report.update') }}" class="modal-content">
                    @csrf
                    <input type="hidden" name="user_id" id="dailyReportUserId">
                    <input type="hidden" name="date" id="dailyReportDate">

                    <div class="modal-header">
                        <h5 class="modal-title" id="dailyReportEditModalLabel">Edit Daily Hours</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">User</label>
                            <input type="text" class="form-control" id="dailyReportUserName" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="text" class="form-control" id="dailyReportDateText" readonly>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hours</label>
                                <input type="number" min="0" max="23" name="hours"
                                    id="dailyReportHours" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Minutes</label>
                                <input type="number" min="0" max="59" name="minutes"
                                    id="dailyReportMinutes" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

@endsection

@push('styles')
    <style>
        .table-scroll-top {
            overflow-x: auto;
            overflow-y: hidden;
        }

        .table-scroll-top div {
            height: 1px;
        }

        .table-scroll-bottom {
            overflow-x: auto;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // -----------START: code for top scroll and bottom scroll sync----------------

        let topScroll;
        let bottomScroll;

        // ✅ Make function global
        function syncWidth() {
            if (!topScroll || !bottomScroll) return;

            const table = bottomScroll.find('table');
            const tableWidth = table.outerWidth();
            const containerWidth = bottomScroll.outerWidth();

            topScroll.find('div').width(tableWidth);

            if (tableWidth > containerWidth) {
                topScroll.show();
            } else {
                topScroll.hide();
            }
        }
        $(document).ready(function() {
            $('[title]').tooltip();


            topScroll = $('.table-scroll-top');
            bottomScroll = $('.table-scroll-bottom');

            syncWidth();

            // Sync scroll positions
            topScroll.on('scroll', function() {
                bottomScroll.scrollLeft($(this).scrollLeft());
            });

            bottomScroll.on('scroll', function() {
                topScroll.scrollLeft($(this).scrollLeft());
            });

            // Recalculate on window resize
            $(window).on('resize', syncWidth);

            // -----------END: code for top scroll and bottom scroll sync----------------
        });

        $('#userFilter').change(function() {
            let user = $(this).val();
            if (user == '') {
                $('td,th').show();
                $('.report-table th:first-child, .report-table td:first-child').css('width', 'auto');
                $('.report-table th[data-user], .report-table td[data-user]').css('width', 'auto');

            } else {
                $('td[data-user],th[data-user]').hide();
                $('td[data-user=' + user + '],th[data-user=' + user + ']').show();

                $('.report-table th:first-child, .report-table td:first-child').css('width', '50%');
                $('.report-table th[data-user=' + user + '], .report-table td[data-user=' + user + ']').css('width',
                    '50%');
            }

            // $('.table-scroll-top').toggle();
            syncWidth();

        });

        const dailyReportEditModal = document.getElementById('dailyReportEditModal');
        const dailyReportConfirmModal = document.getElementById('dailyReportConfirmModal');
        let dailyReportPendingPayload = null;

        if (dailyReportConfirmModal) {
            dailyReportConfirmModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;

                dailyReportPendingPayload = {
                    userId: button.getAttribute('data-user-id'),
                    userName: button.getAttribute('data-user-name'),
                    date: button.getAttribute('data-date'),
                    hours: button.getAttribute('data-hours'),
                    minutes: button.getAttribute('data-minutes'),
                };

                document.getElementById('dailyReportConfirmUserName').textContent = dailyReportPendingPayload
                    .userName;
                document.getElementById('dailyReportConfirmDate').textContent = dailyReportPendingPayload.date;
            });
        }

        if (dailyReportEditModal) {
            document.getElementById('dailyReportConfirmYes').addEventListener('click', function() {
                if (!dailyReportPendingPayload) {
                    return;
                }

                document.getElementById('dailyReportUserId').value = dailyReportPendingPayload.userId;
                document.getElementById('dailyReportDate').value = dailyReportPendingPayload.date;
                document.getElementById('dailyReportUserName').value = dailyReportPendingPayload.userName;
                document.getElementById('dailyReportDateText').value = dailyReportPendingPayload.date;
                document.getElementById('dailyReportHours').value = dailyReportPendingPayload.hours;
                document.getElementById('dailyReportMinutes').value = dailyReportPendingPayload.minutes;

                bootstrap.Modal.getInstance(dailyReportConfirmModal)?.hide();
                bootstrap.Modal.getOrCreateInstance(dailyReportEditModal).show();
            });
        }
    </script>
@endpush
