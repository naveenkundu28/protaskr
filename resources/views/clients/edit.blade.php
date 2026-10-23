@extends('layouts.app')
@section('title')
    {{ $client->name }} Edit Client
@endsection
@section('content')
    <div class="container-fluid mt-80">
        <h2 class="mb-4 shadow-sm p-3 rounded bg-white">Edit Client</h2>
        <div class="card border-0 shadow-sm m-auto">
            <div class="card-body">
                <form action="{{ route('clients.update', $client->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                            <div class="mb-3 col-sm-12 col-md-6 col-lg-4">
                                <label for="name" class="form-label">Client Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ $client->name }}" required>
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                        <div class="mb-3 col-sm-12 col-md-6 col-lg-4">
                            <label for="email" class="form-label">Client Email <span class="text-danger">*</span></label>
                            <input type="email" name="client_email" id="email" class="form-control"
                                value="{{ $client->client_email }}" required>
                            @error('client_email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        {{-- <div class="mb-3 col-sm-12 col-md-6">
                            <label for="code" class="form-label">Client Code <span class="text-danger">*</span></label>
                            <input type="text" name="client_code" id="code" class="form-control"
                                value="{{ $client->client_code }}" required>
                            @error('client_code')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div> --}}

                            <div class="mb-3 col-sm-12 col-md-4">
                                <label for="type" class="form-label">
                                    Type <span class="text-danger">*</span>
                                </label>
                                <select name="type" id="type" class="form-select" required>
                                    @php
                                        $clientTypeValue = old('type', in_array($client->type, ['client', 'outsourced'], true)
                                            ? $client->type
                                            : 'client');
                                    @endphp
                                    <option value="client" @selected($clientTypeValue === 'client')>Client</option>
                                    <option value="outsourced" @selected($clientTypeValue === 'outsourced')>Outsourced</option>
                                </select>

                                @error('type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3 col-sm-12 col-md-4">
                                <label for="company_name" class="form-label">Company Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="company_name" id="company_name" class="form-control"
                                    value="{{ $client->company_name }}" required>
                                @error('company_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3 col-sm-12 col-md-6 col-lg-4">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="client_status" class="form-select" required>
                                    <option value="not_started" {{ $client->status == 'not_started' ? 'selected' : '' }}>Not
                                        Started</option>
                                    <option value="in_progress" {{ $client->status == 'in_progress' ? 'selected' : '' }}>In
                                        Progress</option>
                                    <option value="completed" {{ $client->status == 'completed' ? 'selected' : '' }}>Completed
                                    </option>
                                </select>
                                @error('status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                        <div class="{{ auth()->user()->can('clients-update') ? 'text-center' : 'text-start' }}">
                            <button type="submit" class="btn btn-primary">Update Client</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- <pre>
        {{ print_r($client) }}
        </pre> --}}
        @if ($client->emailUpdatedBy)
            <div class="card border-0 shadow-sm m-auto mt-4">
                <div class="card-body">
                    <div>
                        Email last updated by
                        <strong>{{ $client->emailUpdatedBy->name }}</strong>
                        on
                        <strong>{{ \Carbon\Carbon::parse($client->email_updated_at)->timezone('Asia/Kolkata')->format('j M Y, h:i A') }}</strong>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            function toggleFields() {

                let status = $('#client_status').val()

                if (status === 'completed') {
                    $('#end_date_div').removeClass('d-none');
                    $('#end_date').attr('required', true);
                } else {
                    $('#end_date_div').addClass('d-none');
                    $('#end_date').val('');
                    $('#end_date').attr('required', false);
                }
            }

            toggleFields()

            $('#client_status').on('change', toggleFields)
        });
    </script>
@endpush
