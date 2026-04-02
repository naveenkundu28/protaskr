@extends('layouts.app')

@section('title')
    Service Listing
@endsection

@section('content')
    <div class="container-fluid">
        <div class="bg-white mb-4 shadow-sm p-3 rounded">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-3 flex-wrap w-100">
                    <h2 class="mb-0">Service Listing</h2>
                </div>
                @can('service-create')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importServicesModal">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                @endcan
            </div>
            <div>
                <form id="serviceListingFilters" method="GET" action="{{ route('services.listing') }}"
                    class="d-flex align-items-start flex-row gap-2 auto-search-form" data-search-target="#tableSearchResults">
                    <input type="hidden" name="tab" value="{{ $activeTab ?? 'active' }}">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search services">
                    <select name="client_id" class="form-select">
                        <option value="">All Clients</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ (string) request('client_id') === (string) $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        @foreach ($monthOptions as $monthOption)
                            <option value="{{ $monthOption['value'] }}" {{ (string) request('month') === (string) $monthOption['value'] ? 'selected' : '' }}>
                                {{ $monthOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <select name="front_resource" class="form-select">
                        <option value="">All Front Resources</option>
                        @foreach ($frontResources as $resource)
                            <option value="{{ $resource->id }}" {{ (string) request('front_resource') === (string) $resource->id ? 'selected' : '' }}>
                                {{ $resource->name }}{{ $resource->employee_id ? ' - ' . $resource->employee_id : '' }}
                            </option>
                        @endforeach
                    </select>
                    {{-- <button class="btn btn-outline-secondary">Search</button> --}}
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('services_file'))
            <div class="alert alert-danger">
                {!! nl2br(e($errors->first('services_file'))) !!}
            </div>
        @endif

        {{-- ================= TABLE ================= --}}
        <ul class="nav nav-pills service-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ ($activeTab ?? 'active') === 'active' ? 'active' : '' }}"
                    href="{{ route('services.listing', [
                        'tab' => 'active',
                        'search' => request('search'),
                        'client_id' => request('client_id'),
                        'month' => request('month'),
                        'front_resource' => request('front_resource'),
                    ]) }}">
                    Active Services
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($activeTab ?? 'active') === 'closed' ? 'active' : '' }}"
                    href="{{ route('services.listing', [
                        'tab' => 'closed',
                        'search' => request('search'),
                        'client_id' => request('client_id'),
                        'month' => request('month'),
                        'front_resource' => request('front_resource'),
                    ]) }}">
                    Closed Services
                </a>
            </li>
        </ul>

        <div id="tableSearchResults">
        <div class="table-responsive">
            <table class="table table-bordered">
                <tr>
                    <th>S.No</th>
                    <th>Client Name</th>
                    <th>Client ID</th>
                    <th>Type</th>
                    <th>Front Resource</th>
                    <th>Working Resource</th>
                    <th>Service ID</th>
                    <th>Month</th>
                    <th>Hours</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    @if (($activeTab ?? 'active') === 'closed')
                        <th>End Date</th>
                        <th style="width:250px">Reason</th>
                    @endif
                    <th>Action</th>
                </tr>

                @foreach ($services as $service)
                    <tr>
                        <td>{{ $services->firstItem() + $loop->index }}</td>
                        <td>{{ $service->client->name ?? '' }}</td>
                        <td>{{ $service->client->client_code ?? $service->client_id }}</td>
                        <td>{{ ucfirst($service->service_type) }}</td>
                        <td>
                            @if ($service->frontResource)
                                <span class="{{ $service->frontResource->trashed() ? 'text-danger' : '' }}">
                                    {{ $service->frontResource->name }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($service->workingResource)
                                <span class="{{ $service->workingResource->trashed() ? 'text-danger' : '' }}">
                                    {{ $service->workingResource->name }}
                                </span>
                            @endif
                        </td>
                        <td>{{ $service->service_id }}</td>
                        <td>{{ $service->created_at ? $service->created_at->format('F') : '' }}</td>

                        @php
                            $year = $service->created_at->format('Y');
                            $month = $service->created_at->format('F');

                            $key = $year . '-' . $month;

                            $masterHour = $masterHours[$key]->hours ?? null;

                            $color = '';

                            if ($masterHour !== null) {
                                if ($service->hours < $masterHour) {
                                    $color = 'text-warning'; // yellow
                                } elseif ($service->hours > $masterHour) {
                                    $color = 'text-primary'; // dark blue
                                }
                            }
                        @endphp

                        <td class="{{ $color }}">
                            {{ $service->hours }}
                        </td>


                        <td>{{ ucfirst(str_replace('_', ' ', $service->service_status)) }}</td>
                        <td>
                            {{ $service->start_date
                                ? \Carbon\Carbon::parse($service->start_date)->format('j F Y')
                                : ($service->main_start_date
                                    ? \Carbon\Carbon::parse($service->main_start_date)->format('j F Y')
                                    : '') }}
                        </td>
                        @if (($activeTab ?? 'active') === 'closed')
                            <td>{{ $service->end_date ? \Carbon\Carbon::parse($service->end_date)->format('j F Y') : '' }}
                            </td>

                            <td style="width:250px">
                                {{ $service->cancel_reason ?? ($service->replacement_reason ?? '') }}
                            </td>
                        @endif

                        <td>
                            <div class="dropdown service-action-dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-list-task"></i>
                                </button>
                                <ul class="dropdown-menu py-0">
                                    @can('service-show')
                                        <li class="">
                                            <a href="{{ route('clients.services.show', [$service->client_id, $service->id]) }}"
                                                class="px-2 py-2 text-primary text-decoration-none d-block">
                                                Show
                                            </a>
                                        </li>
                                    @endcan
                                    @can('service-edit')
                                        <li>
                                            <hr class="my-1" />
                                        </li>
                                        <li class="">
                                            <a href="{{ route('clients.services.index', $service->client_id) }}"
                                                class="px-2 py-2 text-info text-decoration-none d-block">
                                                Manage
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>

        {{ $services->links('pagination::bootstrap-5') }}
        </div>

        @can('service-create')
            <div class="modal fade" id="importServicesModal" tabindex="-1" aria-labelledby="importServicesModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('services.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="importServicesModalLabel">Import Services</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="services_file" class="form-label">Upload Excel File</label>
                                    <input type="file" class="form-control" id="services_file" name="services_file" accept=".xls,.xlsx" required>
                                    <div class="form-text">
                                        Accepted Format: .xlsx, .csv files.
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Import Services</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            @if ($errors->has('services_file'))
                new bootstrap.Modal(document.getElementById('importServicesModal')).show();
            @endif

            const $filtersForm = $('#serviceListingFilters');
            $filtersForm.find('select').on('change', function() {
                $filtersForm.find('input[name="search"]').trigger('input');
            });
        });
    </script>
@endpush
