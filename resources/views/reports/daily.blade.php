@extends('layouts.app')
@section('title','Daily Report')
@section('content')

    <div class="container-fluid">
        <h2>Daily Report</h2>
        
        <div class="row mb-3">
            <div class="col-md-4">
                <form method="GET" action="{{ route('daily.report') }}">
                    <div class="input-group">
                        <input type="month" name="month" class="form-control" value="{{ request('month') ?? date('Y-m') }}">
                        <button class="btn btn-primary">Search</button>
                    </div>
                </form>
            </div>

            <div class="col-md-4">
                <select class="form-control" id="userFilter">
                    <option value="">All Users</option>
                    @foreach($users as $user)

                    <option value="{{ $user->id }}">
                        {{ $user->name }}
                    </option>

                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-sm text-center text-nowrap report-table">
                <thead>
                    <tr class="f-12">
                        <th>#</th>
                        @foreach($users as $key => $user)
                        <th data-user="{{ $user->id }}">
                            {{ $key+1 }}
                        </th>
                        @endforeach
                    </tr>
                    <tr class="f-12"> 
                        <th>Date</th>
                        @foreach($users as $user)
                        @php
                            $name = explode(' ', $user->name);
                            $initials = strtoupper(substr($name[0],0,1) . '_' . substr(end($name),0,1));
                        @endphp
                        <th class="px-4" data-user="{{ $user->id }}" title="{{ $user->name }}">
                            {{ $initials }}
                        </th>
                        @endforeach
                    </tr>
                    <tr class="f-12">
                        <th>Total</th>
                        @foreach($users as $user)
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
                    @foreach($dates as $date)
                    <tr class="f-12">
                        <td>{{ date('d-M-Y (l)',strtotime($date)) }}</td>
                        @foreach($users as $user)
                            @php
                                $report = $reports[$date][$user->id][0] ?? null;
                            @endphp
                            <td data-user="{{ $user->id }}" title="{{ $report && $report->performance ? $user->name . ' - ' . $report->performance->title : $user->name . ' - Absent' }}" style="background-color: {{ $report->performance->background_color ?? '#8b1c1c' }}; color: {{ $report->performance->font_color ?? '#fff' }};">
                                @if($report)
                                   {{ sprintf('%02d:%02d', floor($report->time / 60), $report->time % 60) }}
                                @else
                                    00:00
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                    <tr class="f-12">
                        <th>Total</th>
                        @foreach($users as $user)
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
            @foreach($performances as $p)
                <div style="background: {{ $p->background_color }}; color: {{ $p->font_color }}; padding:6px 12px; border-radius:4px;">
                    {{ $p->title }}
                </div>
            @endforeach
        </div>

    </div>

@endsection

@push('scripts')

<script>

    $(document).ready(function () {
        $('[title]').tooltip();
    });

    $('#userFilter').change(function () {
        let user = $(this).val();
        if (user == '') {
            $('td,th').show();
            $('.report-table th:first-child, .report-table td:first-child').css('width','auto');
            $('.report-table th[data-user], .report-table td[data-user]').css('width','auto');

        } else {
            $('td[data-user],th[data-user]').hide();
            $('td[data-user=' + user + '],th[data-user=' + user + ']').show();
            
            $('.report-table th:first-child, .report-table td:first-child').css('width','50%');
            $('.report-table th[data-user='+user+'], .report-table td[data-user='+user+']').css('width','50%');
        }
    });

</script>

@endpush