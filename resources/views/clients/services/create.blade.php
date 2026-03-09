@extends('layouts.app')

@section('title')
    Add Service
@endsection

@section('content')
    <div class="container">

        <h2 class="mb-4 shadow-sm p-3 bg-white rounded">
            Add Service - {{ $client->name }}
        </h2>

        <div class="card shadow-sm border-0 m-auto" style="max-width:700px">

            <div class="card-body">

                <form action="{{ route('clients.services.store', $client->id) }}" method="POST">

                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Service Type</label>

                        <select name="service_type" class="form-select" required>

                            <option value="direct">Direct</option>
                            <option value="proxy">Proxy</option>
                            <option value="external">External</option>

                        </select>
                        @error('service_type')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Service ID</label>

                        <input type="text" name="service_id" class="form-control" required>
                        @error('service_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hours</label>

                        <input type="number" step="0.01" name="hours" class="form-control">

                        @error('hours')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>

                        <select name="service_status" id="service_status" class="form-select" required>

                            <option value="new">New</option>
                            <option value="continuing">Continuing</option>
                            <option value="closed">Closed</option>

                        </select>

                        @error('service_status')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3" id="start_date_div">
                        <label class="form-label">Start Date</label>

                        <input type="date" name="start_date" class="form-control">

                        @error('start_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3" id="end_date_div">
                        <label class="form-label">End Date</label>

                        <input type="date" name="end_date" class="form-control">

                        @error('end_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Resources</label>

                        <select name="employee[]" class="form-select select2" multiple required>

                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->name }}
                                </option>
                            @endforeach

                        </select>

                        @error('employee')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror

                    </div>

                    <button class="btn btn-primary">
                        Create Service
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
