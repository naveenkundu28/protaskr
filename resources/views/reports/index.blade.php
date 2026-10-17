@extends('layouts.app')
@section('title', 'Reports')

@section('content')
    <div class="container-fluid mt-80">
        <div class="bg-white shadow-sm p-3 rounded mb-4">
            <div class="row">
                <h2 class="col-md-3 mb-0">Reports</h2>

                <div class="col-md-9">
                    <div class="col-lg-9">
                        <form method="GET" action="{{ route('reports.index') }}">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-5">
                                    <label for="from" class="form-label">From</label>
                                    <input type="date" class="form-control" id="from" name="from"
                                        value="{{ $fromDate }}">
                                </div>
                                <div class="col-md-5">
                                    <label for="to" class="form-label">To</label>
                                    <input type="date" class="form-control" id="to" name="to"
                                        value="{{ $toDate }}">
                                </div>
                                <div class="col-md-2 mt-2">
                                    <button type="submit" class="btn btn-primary w-100"
                                        style="padding-top: 7px !important; padding-bottom: 8px !important;">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">New Joinings</div>
                        <div class="fs-4 fw-bold">{{ $newJoiningsCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Resignations</div>
                        <div class="fs-4 fw-bold text-danger">{{ $resignationCount }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion" id="reportsAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingNewClients">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseNewClients" aria-expanded="true" aria-controls="collapseNewClients">
                        New Clients {{ ' -- ' . $newClients->count() }}
                    </button>
                </h2>
                <div id="collapseNewClients" class="accordion-collapse collapse show" aria-labelledby="headingNewClients"
                    data-bs-parent="#reportsAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Client Name</th>
                                        {{-- <th>Client Code</th> --}}
                                        <th>Client Email</th>
                                        <th>Project Name</th>
                                        <th>Assigned To</th>
                                        <th>Start Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($newClients as $index => $client)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $client->name }}</td>
                                            {{-- <td>{{ $client->client_code }}</td> --}}
                                            <td>{{ $client->client_email }}</td>
                                            <td>{{ $client->project_name }}</td>
                                            {{-- <td> 
                                                @isset($client->users)
                                                    @foreach ($client->users as $user)
                                                        <span class="{{ $user->trashed() ? 'text-danger' : '' }}">{{ $user->name }}</span><br>
                                                    @endforeach
                                                @endisset
                                            </td> --}}
                                            <td>
                                                @isset($client->users)
                                                    @php
                                                        $users = $client->users;
                                                        $visibleUsers = $users->take(2);
                                                        $remainingUsers = $users->slice(2);
                                                        $remainingCount = $users->count() - 2;
                                                    @endphp

                                                    <span>
                                                        {{-- Visible Users --}}
                                                        @foreach ($visibleUsers as $user)
                                                            <span class="{{ $user->trashed() ? 'text-danger' : '' }}">
                                                                {{ $user->name }}
                                                            </span>{{ !$loop->last ? ',' : '' }}
                                                        @endforeach

                                                        {{-- Remaining Count --}}
                                                        @if ($remainingCount > 0)
                                                            <span class="text-primary" data-bs-toggle="tooltip"
                                                                style="cursor: pointer;"
                                                                title="@foreach ($remainingUsers as $user){{ $user->name }}{{ !$loop->last ? ', ' : '' }} @endforeach">
                                                                +{{ $remainingCount }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                @endisset
                                            </td>
                                            <td>
                                                {{ $client->start_date ? \Carbon\Carbon::parse($client->start_date)->format('j F Y') : '' }}
                                            </td>
                                            <td>{{ $client->status == 'not_started' ? 'Not Started' : ($client->status == 'in_progress' ? 'In Progress' : 'Completed') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No new clients found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingCurrentClients">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseCurrentClients" aria-expanded="true"
                        aria-controls="collapseCurrentClients">
                        Current Clients {{ ' -- ' . $currentClients->count() }}
                    </button>
                </h2>
                <div id="collapseCurrentClients" class="accordion-collapse collapse show"
                    aria-labelledby="headingCurrentClients" data-bs-parent="#reportsAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Client Name</th>
                                        {{-- <th>Client Code</th> --}}
                                        <th>Client Email</th>
                                        <th>Project Name</th>
                                        <th>Assigned To</th>
                                        <th>Start Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($currentClients as $index => $client)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $client->name }}</td>
                                            {{-- <td>{{ $client->client_code }}</td> --}}
                                            <td>{{ $client->client_email }}</td>
                                            <td>{{ $client->project_name }}</td>
                                            {{-- <td>
                                                @isset($client->users)
                                                    @foreach ($client->users as $user)
                                                        <span
                                                            class="{{ $user->trashed() ? 'text-danger' : '' }}">{{ $user->name }}</span><br>
                                                    @endforeach
                                                @endisset
                                            </td> --}}
                                            <td>
                                                @isset($client->users)
                                                    @php
                                                        $users = $client->users;
                                                        $visibleUsers = $users->take(2);
                                                        $remainingUsers = $users->slice(2);
                                                        $remainingCount = $users->count() - 2;
                                                    @endphp

                                                    <span>
                                                        {{-- Visible Users --}}
                                                        @foreach ($visibleUsers as $user)
                                                            <span class="{{ $user->trashed() ? 'text-danger' : '' }}">
                                                                {{ $user->name }}
                                                            </span>{{ !$loop->last ? ',' : '' }}
                                                        @endforeach

                                                        {{-- Remaining Count --}}
                                                        @if ($remainingCount > 0)
                                                            <span class="text-primary" data-bs-toggle="tooltip"
                                                                style="cursor: pointer;"
                                                                title="@foreach ($remainingUsers as $user){{ $user->name }}{{ !$loop->last ? ', ' : '' }} @endforeach">
                                                                +{{ $remainingCount }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                @endisset
                                            </td>
                                            <td>
                                                {{ $client->start_date ? \Carbon\Carbon::parse($client->start_date)->format('j F Y') : '' }}
                                            </td>
                                            <td>{{ $client->status == 'not_started' ? 'Not Started' : ($client->status == 'in_progress' ? 'In Progress' : 'Completed') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No current clients found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingNewServices">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseNewServices" aria-expanded="true" aria-controls="collapseNewServices">
                        New Services {{ ' -- ' . $newServices->count() }}
                    </button>
                </h2>
                <div id="collapseNewServices" class="accordion-collapse collapse show" aria-labelledby="headingNewServices"
                    data-bs-parent="#reportsAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Service ID</th>
                                        <th>Resource Id</th>
                                        <th>Working Resource</th>
                                        <th>Work Type</th>
                                        <th>Other Resources</th>
                                        <th>Type</th>
                                        <th>Client</th>
                                        <th>Hours</th>
                                        <th>Total Hours</th>

                                        <th>Start Date</th>
                                        {{-- <th>Created</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($newServices as $index => $service)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $service->service_id }}</td>
                                            <td>{{ $service->working_users->pluck('employee_id')->filter()->implode(', ') ?: '-' }}</td>
                                            <td>{{ $service->working_users->pluck('name')->implode(', ') ?: '-' }}</td>
                                            <td>{{ $service->service_work_type }}</td>

                                            <td class="p-0">
                                                @if (!empty($service->service_other_resources))
                                                    <table class="table table-sm table-bordered mb-0">

                                                        <tbody>
                                                            @foreach ($service->service_other_resources as $res)
                                                                <tr>
                                                                    <td>{{ $res['resource_name'] ?? '-' }}</td>
                                                                    <td>{{ $res['total_hours'] ? $res['total_hours'] . ' Hrs' : 0 }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            <td>{{ ucfirst($service->service_type) }}</td>
                                            <td>{{ $service->client?->name }}</td>
                                            <td>{{ $service->hours }}</td>
                                            <td>{{ number_format($service->service_total_hours, 2) }}</td>

                                            <td> {{ $service->start_date ? \Carbon\Carbon::parse($service->start_date)->format('j F Y') : '' }}
                                            </td>
                                            {{-- <td>{{ optional($service->created_at)->format('d-m-Y') }}</td> --}}
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center text-muted">No new services found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingCompletedServices">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseCompletedServices" aria-expanded="true"
                        aria-controls="collapseCompletedServices">
                        Completed Services {{ ' -- ' . $completedServices->count() }}
                    </button>
                </h2>
                <div id="collapseCompletedServices" class="accordion-collapse collapse show"
                    aria-labelledby="headingCompletedServices" data-bs-parent="#reportsAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Service ID</th>
                                        <th>Resource Id</th>
                                        <th>Working Resource</th>
                                        <th>Work Type</th>
                                        <th>Other Resources</th>
                                        <th>Type</th>
                                        <th>Client</th>
                                        <th>Hours</th>
                                        <th>Total Hours</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th style="width:250px;">Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($completedServices as $index => $service)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $service->service_id }}</td>
                                            <td>{{ $service->working_users->pluck('employee_id')->filter()->implode(', ') ?: '-' }}</td>
                                            <td>{{ $service->working_users->pluck('name')->implode(', ') ?: '-' }}</td>
                                            <td>{{ $service->service_work_type }}</td>
                                            <td class="p-0">
                                                @if (!empty($service->service_other_resources))
                                                    <table class="table table-sm table-bordered mb-0">

                                                        <tbody>
                                                            @foreach ($service->service_other_resources as $res)
                                                                <tr>
                                                                    <td>{{ $res['resource_name'] ?? '-' }}</td>
                                                                    <td>{{ $res['total_hours'] ? $res['total_hours'] . ' Hrs' : 0 }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ ucfirst($service->service_type) }}</td>
                                            <td>{{ $service->client?->name }}</td>
                                            <td>{{ $service->hours }}</td>
                                            <td>{{ number_format($service->service_total_hours, 2) }}</td>
                                            <td> {{ $service->start_date ? \Carbon\Carbon::parse($service->start_date)->format('j F Y') : '' }}
                                            </td>
                                            <td>{{ $service->end_date ? \Carbon\Carbon::parse($service->start_date)->format('j F Y') : '' }}
                                            </td>
                                            <td>Service Completed</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center text-muted">No completed services found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingCancelledServices">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseCancelledServices" aria-expanded="true"
                        aria-controls="collapseCancelledServices">
                        Cancelled Services {{ ' -- ' . $cancelledServices->count() }}
                    </button>
                </h2>
                <div id="collapseCancelledServices" class="accordion-collapse collapse show"
                    aria-labelledby="headingCancelledServices" data-bs-parent="#reportsAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Service ID</th>
                                        <th>Resource Id</th>
                                        <th>Working Resource</th>
                                        <th>Work Type</th>
                                        <th>Other Resources</th>
                                        <th>Type</th>
                                        <th>Client</th>
                                        <th>Hours</th>
                                        <th>Total Hours</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th style="width:250px;">Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cancelledServices as $index => $service)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $service->service_id }}</td>
                                            <td>{{ $service->working_users->pluck('employee_id')->filter()->implode(', ') ?: '-' }}</td>
                                            <td>{{ $service->working_users->pluck('name')->implode(', ') ?: '-' }}</td>
                                            <td>{{ $service->service_work_type }}</td>
                                            <td class="p-0">
                                                @if (!empty($service->service_other_resources))
                                                    <table class="table table-sm table-bordered mb-0">

                                                        <tbody>
                                                            @foreach ($service->service_other_resources as $res)
                                                                <tr>
                                                                    <td>{{ $res['resource_name'] ?? '-' }}</td>
                                                                    <td>{{ $res['total_hours'] ? $res['total_hours'] . ' Hrs' : 0 }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ ucfirst($service->service_type) }}</td>
                                            <td>{{ $service->client?->name }}</td>
                                            <td>{{ $service->hours }}</td>
                                            <td>{{ number_format($service->service_total_hours, 2) }}</td>
                                            <td> {{ $service->start_date ? \Carbon\Carbon::parse($service->start_date)->format('j F Y') : '' }}
                                            </td>
                                            <td>{{ $service->end_date ? \Carbon\Carbon::parse($service->start_date)->format('j F Y') : '' }}
                                            </td>
                                            <td>{{ $service->service_status == 'canceled' ? 'Cancelled - ' . $service->cancel_reason : 'Replacement - ' . $service->replacement_reason }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center text-muted">No cancelled services found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
@endsection
