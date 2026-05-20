@extends('layouts.app')
@section('title')
    Create Client
@endsection
@section('content')
    <div class="container-fluid">
        <h2 class="mb-4 shadow-sm p-3 rounded bg-white">Create Client</h2>
        <div class="card border-0 shadow-sm m-auto">
            <div class="card-body">
                <form action="{{ route('clients.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-sm-12 col-md-6 col-lg-4">
                            <label for="employee" class="form-label">Employee <span class="text-danger">*</span></label>
                            <select name="employee[]" id="employee" class="form-select select2" required multiple>
                                {{-- <option value="">Select Employee</option> --}}
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                            @error('employee')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3 col-sm-12 col-md-6 col-lg-4">
                            <label for="name" class="form-label">Client Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3 col-sm-12 col-md-6 col-lg-4">
                            <label for="email" class="form-label">Client Email <span class="text-danger">*</span></label>
                            <input type="email" name="client_email" id="email" class="form-control" required>
                            @error('client_email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        {{-- <div class="mb-3 col-sm-12 col-md-6">
                            <label for="code" class="form-label">Client Code <span class="text-danger">*</span></label>
                            <input type="text" name="client_code" id="code" class="form-control" required>
                            @error('client_code')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div> --}}
                        <div class="mb-3 col-sm-12 col-md-4">
                            <label for="type" class="form-label">
                                Type <span class="text-danger">*</span>
                            </label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="internal_team">Internal Team</option>
                                <option value="external_team">External Team</option>
                                <option value="client" selected>Client</option>
                                <option value="project">Project</option>
                            </select>

                            @error('type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3 col-sm-12 col-md-4">
                            <label for="project_name" class="form-label">Project Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="project_name" id="project_name" class="form-control" required>
                            @error('project_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="mb-3 col-sm-12 col-md-6 col-lg-4">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                            @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="mb-3 col-sm-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="summernote" class="form-control"></textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="mb-3 col-sm-12 col-md-6 col-lg-4" id="end_date_div">
                            <label for="end_date" class="form-label">End Date<span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                            @error('end_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3 col-sm-12 col-md-6 col-lg-4">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="client_status" class="form-select" required>
                                <option value="not_started">Not Started</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3 col-sm-12 col-md-6 col-lg-4">
                            <label for="budget" class="form-label">Budget</label>
                            <input type="number" name="budget" id="budget" class="form-control" step="0.01">
                            @error('budget')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary mt-2">Create Client</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            function toggleFields() {

                let status = $('#client_status').val()

                if (status === 'completed') {
                    $('#end_date_div').removeClass('d-none');
                } else {
                    $('#end_date_div').addClass('d-none');
                    $('#end_date').val('');
                }
            }

            toggleFields()

            $('#client_status').on('change', toggleFields)
        });
    </script>
@endpush
