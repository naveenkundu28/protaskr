@extends('layouts.app')

@section('title')
    Edit Service
@endsection

@section('content')
    <div class="container">

        <h2 class="mb-4 shadow-sm p-3 bg-white rounded">
            Edit Service
        </h2>

        <div class="card border-0 shadow-sm m-auto" style="max-width:700px">

            <div class="card-body">

                <form action="{{ route('clients.services.update', [$client->id, $service->id]) }}" method="POST">

                    @csrf
                    @method('PUT')

                    <div class="mb-3">

                        <label>Service Type</label>

                        <select name="service_type" class="form-select">

                            <option value="direct" {{ $service->service_type == 'direct' ? 'selected' : '' }}>
                                Direct
                            </option>

                            <option value="proxy" {{ $service->service_type == 'proxy' ? 'selected' : '' }}>
                                Proxy
                            </option>

                            <option value="external" {{ $service->service_type == 'external' ? 'selected' : '' }}>
                                External
                            </option>

                        </select>

                        @error('service_type')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror

                    </div>

                    <div class="mb-3">

                        <label>Service ID</label>

                        <input type="text" name="service_id" class="form-control" value="{{ $service->service_id }}">
                        @error('service_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">

                        <label>Hours</label>

                        <input type="number" step="0.01" name="hours" class="form-control"
                            value="{{ $service->hours }}">
                        @error('hours')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">

                        <label>Status</label>

                        <select name="service_status" id="service_status" class="form-select">

                            <option value="new" {{ $service->service_status == 'new' ? 'selected' : '' }}>
                                New
                            </option>

                            <option value="continuing" {{ $service->service_status == 'continuing' ? 'selected' : '' }}>
                                Continuing
                            </option>

                            <option value="closed" {{ $service->service_status == 'closed' ? 'selected' : '' }}>
                                Closed
                            </option>

                        </select>

                        @error('service_status')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror

                    </div>

                    <div class="mb-3" id="start_date_div">

                        <label>Start Date</label>

                        <input type="date" name="start_date" class="form-control" value="{{ $service->start_date }}">
                        @error('start_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3" id="end_date_div">

                        <label>End Date</label>

                        <input type="date" name="end_date" class="form-control" value="{{ $service->end_date }}">
                        @error('end_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">

                        <label>Employees</label>

                        <select name="employee[]" class="form-select select2" multiple>

                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}"
                                    {{ $service->users->contains($employee->id) ? 'selected' : '' }}>

                                    {{ $employee->name }}

                                </option>
                            @endforeach

                        </select>

                        @error('employee')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror

                    </div>

                    <button class="btn btn-primary">
                        Update Service
                    </button>

                    <a href="{{ route('clients.services.index', $client->id) }}" class="btn btn-secondary">
                        Back
                    </a>

                </form>

            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {

            function toggleDates() {

                let status = $('#service_status').val()

                $('#start_date_div').hide()
                $('#end_date_div').hide()

                if (status === 'new') {
                    $('#start_date_div').show()
                }

                if (status === 'closed') {
                    $('#end_date_div').show()
                }

            }

            toggleDates()

            $('#service_status').on('change', toggleDates)

        });
    </script>
@endpush
