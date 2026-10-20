@extends('layouts.app')
@section('title', 'Hourly Report')
@section('content')

    @php
        $hourlyUserCount = $users->count();
        $hourlyDensity = $hourlyUserCount >= 25 ? 'hourly-report-table--compact' : ($hourlyUserCount >= 21 ? 'hourly-report-table--standard' : 'hourly-report-table--roomy');
    @endphp

    <div class="container-fluid mt-80 hourly-report-page">
        <div class="hourly-report-heading">
            <div>
                <span class="hourly-report-eyebrow">TIME TRACKING</span>
                <h1>Hourly Report</h1>
                <p>Review daily hours and performance across your team.</p>
            </div>
        </div>


        <div class="hourly-report-toolbar">
            <form method="GET" class="hourly-report-filter" action="{{ route('reports.hourly.index') }}">
                <label for="hourlyMonth">Report month</label>
                <div class="input-group">
                    <input id="hourlyMonth" type="month" name="month" class="form-control" value="{{ request('month') ?? date('Y-m') }}">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i> Search</button>
                </div>
            </form>

            <div class="hourly-report-filter">
                <label for="userFilter">Team member</label>
                <select class="form-select" id="userFilter">
                    <option value="">All users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            @if (auth()->user()?->can('reports-hourly-update'))
                <form method="POST" class="hourly-report-filter hourly-report-fetch" action="{{ route('reports.hourly.generate-weekly') }}">
                    @csrf
                    <label for="hourlyFetchDate">Fetch report through</label>
                    <div class="input-group">
                        <input id="hourlyFetchDate" type="date" name="report_date" class="form-control" max="{{ now()->subDay()->format('Y-m-d') }}"
                            value="{{ now()->subDay()->format('Y-m-d') }}">
                        <button type="submit" class="btn btn-info text-white">
                            <i class="bi bi-arrow-repeat me-1"></i> Fetch
                        </button>
                    </div>
                </form>
            @endif
        </div>

        <div class="table-responsive table-scroll-bottom hourly-report-table-card">

            <table class="table table-bordered table-sm text-center text-nowrap report-table hourly-report-table {{ $hourlyDensity }}">
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
                                $firstName = explode(' ', trim($user->name))[0] ?? $user->name;
                                $tagColor = $user->color ?: '#6c757d';
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
                            <th class="hourly-report-user-heading" data-user="{{ $user->id }}" title="{{ $user->name }}" style="font-weight: 600; background-color: {{ $tagColor }}; color: {{ $textColor }};">
                                {{ $firstName }}
                            </th>
                        @endforeach
                    </tr>
                    <tr class="f-12">
                        <th>Total</th>
                        @foreach ($users as $user)
                            <th data-user="{{ $user->id }}" data-user-name="{{ explode(' ', trim($user->name))[0] ?? $user->name }}">
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
                                <td data-user="{{ $user->id }}" data-user-name="{{ explode(' ', trim($user->name))[0] ?? $user->name }}" class="daily-report-cell p-0"
                                    title="{{ $report && $report->performance ? $user->name . ' - ' . $report->performance->title : $user->name . ' - Absent' }}"
                                    style="background-color: {{ $report->performance->background_color ?? '#8b1c1c' }}; color: {{ $report->performance->font_color ?? '#fff' }};">
                                    <button type="button"
                                        class="btn btn-link daily-report-trigger text-decoration-none p-0 border-0 w-100 h-100"
                                        @if (auth()->user()?->can('reports-hourly-update')) data-bs-toggle="modal"
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
                    <tr class="f-12 hourly-report-total-row">
                        <th>Total</th>
                        @foreach ($users as $user)
                            <th data-user="{{ $user->id }}" data-user-name="{{ explode(' ', trim($user->name))[0] ?? $user->name }}">
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

        <h5 class="hourly-report-legend-title">Performance Legend</h5>
        <div class="hourly-report-legend">
            @foreach ($performances as $p)
                <div
                    style="background: {{ $p->background_color }}; color: {{ $p->font_color }}; padding:6px 12px; border-radius:4px;">
                    {{ $p->title }}
                </div>
            @endforeach
        </div>
        <h5 class="hourly-report-legend-title">Tags Legend</h5>
        <div class="hourly-report-legend">
            @foreach ($tags as $p)
                @php
                    $tagColor = $p->color ?: '#6c757d';
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
                <div
                    style="background: {{ $tagColor }}; color: {{ $textColor }}; padding:6px 12px; border-radius:4px; font-weight:600;">
                    {{ $p->name }}
                </div>
            @endforeach
        </div>
    </div>

    @if (auth()->user()?->can('reports-hourly-update'))
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
                <form method="POST" action="{{ route('reports.hourly.update') }}" class="modal-content">
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

@push('scripts')
    <script>
        function updateHourlyReportDensity() {
            const table = $('.hourly-report-table');
            const visibleUserCount = $('#userFilter').val() ? 1 : table.find('thead tr:nth-child(2) th[data-user]').length;
            const densityClass = visibleUserCount >= 25 ? 'hourly-report-table--compact' :
                (visibleUserCount >= 21 ? 'hourly-report-table--standard' : 'hourly-report-table--roomy');

            table.removeClass('hourly-report-table--compact hourly-report-table--standard hourly-report-table--roomy')
                .addClass(densityClass);
        }

        $(document).ready(function() {
            $('[title]').tooltip();
            updateHourlyReportDensity();
        });

        $('#userFilter').change(function() {
            const user = $(this).val();
            const userCells = $('.hourly-report-table [data-user]');

            userCells.removeClass('hourly-report-user-hidden');
            if (user !== '') {
                userCells.not('[data-user="' + user + '"]').addClass('hourly-report-user-hidden');
            }

            updateHourlyReportDensity();
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
