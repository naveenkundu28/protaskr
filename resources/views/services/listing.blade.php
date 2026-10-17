@extends('layouts.app')

@section('title', 'Service Listing')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/project-module.css') }}">
    <style>
        .inner_page_topbar h1 {
            white-space: nowrap;
            margin: 0;
            font-size: 2rem;
            font-weight: 750;
        }
        .inner_page_topbar .right_search_box {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            flex-wrap: nowrap;
        }
        .inner_page_topbar .search_box {
            position: relative;
            margin: 0;
        }
        .inner_page_topbar .search_box input {
            background: #fff;
            outline: none;
            border: 1px solid #ddd;
            padding: 10px 36px 10px 42px;
            font-size: 14px;
            min-width: 270px;
            border-radius: 10px;
            color: #010132;
            height: 42px;
            transition: all .15s ease;
        }
        .inner_page_topbar .search_box input:focus {
            border-color: #1469e8;
            box-shadow: 0 0 0 3px rgba(20, 105, 232, 0.12);
        }
        .inner_page_topbar .search_box button {
            position: absolute;
            left: 14px;
            top: 50% !important;
            transform: translateY(-50%) !important;
            background: transparent;
            border: 0;
            padding: 0;
            color: #64748b;
            cursor: pointer;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .search_clear_link {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            background: transparent;
            border: none;
            padding: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            text-decoration: none;
            border-radius: 50%;
            transition: all .15s ease;
        }
        .search_clear_link:hover {
            color: #0f172a;
            background: #f1f5f9;
        }
        .filter-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            height: 42px;
            padding: 0 16px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fff;
            color: #46536b;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            transition: all .15s ease;
        }
        .filter-toggle-btn:hover,
        .filter-toggle-btn:focus,
        .filter-toggle-btn[aria-expanded="true"] {
            background: #f8faff;
            border-color: #1469e8;
            color: #1469e8;
        }
        .filter-toggle-btn.has-filter {
            background: #eef5ff;
            border-color: #1469e8;
            color: #1469e8;
            font-weight: 600;
        }
        .filter-badge-count {
            background: #1469e8;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
            line-height: 1.2;
        }
        .toggle-chevron {
            font-size: 11px;
            color: #94a3b8;
            transition: transform .2s ease;
        }
        .filter-toggle-btn[aria-expanded="true"] .toggle-chevron {
            transform: rotate(180deg);
        }
        .pm-filter-panel {
            background: #fff;
            border: 1px solid #e6edf7;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 22px;
            box-shadow: 0 8px 24px rgba(20, 105, 232, 0.05);
        }
        .pm-filter-panel-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #46536b;
        }
        .pm-filter-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            margin-bottom: 6px;
            display: block;
        }
        .pm-filter-input {
            height: 42px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            color: #17243d;
            padding: 0 14px;
            background-color: #fff;
            transition: all .15s ease;
        }
        .pm-filter-input:focus {
            border-color: #1469e8;
            box-shadow: 0 0 0 3px rgba(20, 105, 232, 0.12);
            outline: none;
        }
        .pm-button {
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 10px !important;
            padding: 0 18px !important;
            font-weight: 600;
            white-space: nowrap;
        }
        .service-id-badge {
            font-family: monospace;
            font-size: 12px;
            background: #f1f5f9;
            color: #334155;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }
        @media (max-width: 991px) {
            .inner_page_topbar {
                flex-direction: column;
                align-items: stretch;
                gap: 16px;
            }
            .inner_page_topbar .right_search_box {
                flex-wrap: wrap;
                justify-content: flex-start;
            }
            .inner_page_topbar .search_box input {
                min-width: 100%;
            }
        }
    </style>
@endpush

@section('content')
<div class="container-fluid mt-80">
    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->has('services_file'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {!! nl2br(e($errors->first('services_file'))) !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Top heading & search/action bar --}}
    <div class="pm-heading">
        <div class="inner_page_topbar">
            <h1>Service Listing</h1>
            <div class="right_search_box">
                {{-- Search Box (Identical to Projects & Clients listing) --}}
                <div class="search_box">
                    <button type="button" id="topbarSearchBtn" aria-label="Search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"></path>
                        </svg>
                    </button>
                    <input type="text" id="topbarSearchInput" value="{{ request('search') }}" placeholder="Search services...">
                    @if (request('search'))
                        <a href="{{ route('services.listing.index', array_filter(['tab' => $activeTab ?? 'active', 'client_id' => request('client_id'), 'month' => request('month'), 'front_resource' => request('front_resource')])) }}" class="search_clear_link" title="Clear search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>

                {{-- Month Filter --}}
                <form id="serviceListingFilters" method="GET" action="{{ route('services.listing.index') }}" class="m-0">
                    <input type="hidden" name="tab" value="{{ $activeTab ?? 'active' }}">
                    <input type="hidden" name="search" id="filterHiddenSearch" value="{{ request('search') }}">
                    <input type="hidden" name="client_id" value="{{ request('client_id') }}">
                    <input type="hidden" name="front_resource" value="{{ request('front_resource') }}">
                    <label for="serviceMonthFilter" class="visually-hidden">Service month</label>
                    <input type="month" name="month" id="serviceMonthFilter" class="form-control pm-filter-input"
                        value="{{ $selectedMonth }}" aria-label="Filter services by month" onchange="this.form.submit()">
                </form>

                {{-- Import Button --}}
                @can('services-listing-import')
                    <button type="button" class="btn btn-primary pm-button" data-bs-toggle="modal" data-bs-target="#importServicesModal">
                        <i class="bi bi-upload"></i> Import
                    </button>
                @endcan
            </div>
        </div>
    </div>

    {{-- Tabs (Active Services, Closed Services) --}}
    <div class="pm-tabs">
        <a class="{{ ($activeTab ?? 'active') === 'active' ? 'active' : '' }}"
            href="{{ route('services.listing.index', array_filter([
                'tab' => 'active',
                'search' => request('search'),
                'client_id' => request('client_id'),
                'month' => request('month'),
                'front_resource' => request('front_resource'),
            ])) }}">
            Active Services
        </a>
        <a class="{{ ($activeTab ?? 'active') === 'closed' ? 'active' : '' }}"
            href="{{ route('services.listing.index', array_filter([
                'tab' => 'closed',
                'search' => request('search'),
                'client_id' => request('client_id'),
                'month' => request('month'),
                'front_resource' => request('front_resource'),
            ])) }}">
            Closed Services
        </a>
    </div>

    {{-- Active filter tags (if search or filters applied) --}}
    @php
        $selectedClient = request('client_id') ? $clients->firstWhere('id', request('client_id')) : null;
        $selectedResource = request('front_resource') ? $frontResources->firstWhere('id', request('front_resource')) : null;
    @endphp
    @if (request('search') || request('client_id') || request('front_resource'))
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <span class="text-muted small">Active Filters:</span>
            @if (request('search'))
                <span class="badge bg-light text-dark border py-1 px-2 d-inline-flex align-items-center gap-1">
                    Search: "{{ request('search') }}"
                    <a href="{{ route('services.listing.index', array_filter(['tab' => $activeTab ?? 'active', 'client_id' => request('client_id'), 'month' => request('month'), 'front_resource' => request('front_resource')])) }}" class="text-muted text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            @if ($selectedClient)
                <span class="badge bg-light text-primary border py-1 px-2 d-inline-flex align-items-center gap-1">
                    Client: {{ $selectedClient->name }}
                    <a href="{{ route('services.listing.index', array_filter(['tab' => $activeTab ?? 'active', 'search' => request('search'), 'month' => request('month'), 'front_resource' => request('front_resource')])) }}" class="text-primary text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            @if ($selectedResource)
                <span class="badge bg-light text-primary border py-1 px-2 d-inline-flex align-items-center gap-1">
                    Resource: {{ $selectedResource->name }}
                    <a href="{{ route('services.listing.index', array_filter(['tab' => $activeTab ?? 'active', 'search' => request('search'), 'client_id' => request('client_id'), 'month' => request('month')])) }}" class="text-primary text-decoration-none ms-1">&times;</a>
                </span>
            @endif
            <a href="{{ route('services.listing.index', ['tab' => $activeTab ?? 'active']) }}" class="btn btn-link btn-sm text-danger p-0 text-decoration-none small ms-1">
                <i class="bi bi-x-circle"></i> Clear All
            </a>
        </div>
    @endif

    {{-- ================= AJAX TABLE CONTAINER ================= --}}
    <div id="tableSearchResults">
        <div class="pm-card">
            <div class="table-responsive project_table">
                <table class="table pm-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client Name</th>
                            {{-- <th>Client ID</th> --}}
                            <th>Type</th>
                            <th>Front Resource</th>
                            <th>Working Resource</th>
                            <th>Service ID</th>
                            {{-- <th>Month</th> --}}
                            <th>Hours</th>
                            {{-- <th>Status</th> --}}
                            <th>Start Date</th>
                            @if (($activeTab ?? 'active') === 'closed')
                                <th>End Date</th>
                                <th style="width:250px">Reason</th>
                            @endif
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($services as $service)
                            <tr>
                                <td>{{ $services->firstItem() + $loop->index }}</td>
                                <td>
                                    @if ($service->client)
                                        <a class="pm-link fw-semibold" href="{{ route('clients.show', $service->client_id) }}">
                                            {{ $service->client->name }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                {{-- <td>{{ $service->client->client_code ?? $service->client_id }}</td> --}}
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ ucfirst($service->service_type) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($service->frontResource)
                                        <span class="{{ $service->frontResource->trashed() ? 'text-danger' : '' }}">
                                            {{ $service->frontResource->name }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @include('services.partials.working-resources', ['service' => $service, 'empty' => '—'])
                                </td>
                                <td>
                                    <span class="service-id-badge">{{ $service->service_id }}</span>
                                </td>
                                {{-- <td>{{ $service->month ? \Carbon\Carbon::create()->month($service->month)->format('M') : $service->created_at->format('M') }}</td> --}}

                                @php
                                    $year = $service->year ?? $service->created_at->format('Y');
                                    $month = $service->month ?? $service->created_at->format('F');
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
                                <td>
                                    <span class="fw-bold {{ $color }}">
                                        {{ $service->hours }}
                                    </span>
                                </td>
                                {{-- <td>
                                    <span class="pm-badge {{ $service->service_status }}">
                                        {{ ucfirst(str_replace('_', ' ', $service->service_status)) }}
                                    </span>
                                </td> --}}
                                <td>
                                    {{ $service->start_date
                                        ? \Carbon\Carbon::parse($service->start_date)->format('d M Y')
                                        : ($service->main_start_date
                                            ? \Carbon\Carbon::parse($service->main_start_date)->format('d M Y')
                                            : '—') }}
                                </td>
                                @if (($activeTab ?? 'active') === 'closed')
                                    <td>
                                        {{ $service->end_date ? \Carbon\Carbon::parse($service->end_date)->format('d M Y') : '—' }}
                                    </td>
                                    <td style="width:250px">
                                        {{ $service->cancel_reason ?? ($service->replacement_reason ?? '—') }}
                                    </td>
                                @endif
                                <td>
                                    <div class="project-actions">
                                        <button type="button" class="emp_status_action" aria-label="Service actions">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul>
                                            @can('service-show')
                                                <li>
                                                    <a href="{{ route('clients.services.show', [$service->client_id, $service->id]) }}">
                                                        <i class="bi bi-eye me-1 text-primary"></i> Show
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('service-update')
                                                <li>
                                                    <a href="{{ route('clients.services.index', $service->client_id) }}">
                                                        <i class="bi bi-gear me-1 text-info"></i> Manage
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ ($activeTab ?? 'active') === 'closed' ? 14 : 12 }}" class="text-center py-5 text-muted">
                                    No services match your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination matching Projects & Clients layout --}}
            {{ $services->links('pagination.pm', ['entity' => 'Services']) }}
        </div>
    </div>

    {{-- Import Modal --}}
    @can('services-listing-import')
        <div class="modal fade" id="importServicesModal" tabindex="-1" aria-labelledby="importServicesModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('services.listing.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="importServicesModalLabel">Import Services</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="services_file" class="form-label">Upload Excel File</label>
                                <input type="file" class="form-control" id="services_file" name="services_file" accept=".xls,.xlsx,.csv" required>
                                <div class="form-text">
                                    Accepted Format: .xlsx, .xls, .csv files.
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
            const $topbarSearch = $('#topbarSearchInput');
            const $hiddenSearch = $('#filterHiddenSearch');

            // Keep hidden search input in sync with topbar search
            $topbarSearch.on('input', function() {
                $hiddenSearch.val($(this).val());
                $hiddenSearch.trigger('input');
            });

            $topbarSearch.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $hiddenSearch.val($(this).val());
                    $filtersForm.trigger('submit');
                    $hiddenSearch.trigger('input');
                }
            });

        });
    </script>
@endpush
