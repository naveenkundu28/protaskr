@extends('layouts.app')

@section('title')
    Service Listing
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center bg-white mb-4 shadow-sm p-3 rounded">
            <h2>Service Listing</h2>
            @can('service-create')
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importServicesModal">
                    <i class="bi bi-upload me-1"></i> Import
                </button>
            @endcan
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
                    <th>End Date</th>
                    <th style="width:250px">Reason</th>
                    <th>Action</th>
                </tr>

                @foreach ($services as $service)
                    <tr>
                        <td>{{ $services->firstItem() + $loop->index }}</td>
                        <td>{{ $service->client->name ?? '' }}</td>
                        <td>{{ $service->client->client_code ?? $service->client_id }}</td>
                        <td>{{ ucfirst($service->service_type) }}</td>
                        <td>{{ $service->frontResource->name ?? '' }}</td>
                        <td>{{ $service->workingResource->name ?? '' }}</td>
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
                        <td>{{ $service->end_date ? \Carbon\Carbon::parse($service->end_date)->format('j F Y') : '' }}</td>
                        <td style="width:250px">
                            {{ $service->cancel_reason ?? ($service->replacement_reason ?? '') }}
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-list-task"></i>
                                </button>
                                <ul class="dropdown-menu py-0">
                                    @can('service-show')
                                        <li class="px-2 py-2">
                                            <a href="{{ route('clients.services.show', [$service->client_id, $service->id]) }}"
                                                class="px-1 text-primary text-decoration-none d-block">
                                                Show
                                            </a>
                                        </li>
                                    @endcan
                                    @can('service-edit')
                                        <li>
                                            <hr class="my-1" />
                                        </li>
                                        <li class="px-2 py-2">
                                            <a href="{{ route('clients.services.index', $service->client_id) }}"
                                                class="px-1 text-info text-decoration-none d-block">
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
                                        Accepted columns: client name, client_id, type, front_resource_id, back_end_resource_id, service_id, hours, status, start/end dates, reason fields.
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
        });
    </script>
@endpush
