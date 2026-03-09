@extends('layouts.app')
@section('title')
    {{ $client->name }} Edit Client
@endsection
@section('content')
    <div class="container">
        <h2 class="mb-4 shadow-sm p-3 rounded bg-white">Edit Client</h2>
        <div class="card border-0 shadow-sm m-auto" style="max-width: 600px;">
            <div class="card-body">
                <form action="{{ route('clients.update', $client->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="employee" class="form-label">Employee</label>
                        <select name="employee[]" id="employee" class="form-select select2" required multiple>
                            {{-- <option value="">Select Employee</option> --}}
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}"
                                {{ in_array($employee->id, $client->teamProjects->pluck('id')->toArray()) ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('employee')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Client Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ $client->name }}"
                            required>
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Client Email</label>
                        <input type="email" name="client_email" id="email" class="form-control" value="{{ $client->client_email }}" required>
                        @error('client_email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="code" class="form-label">Client Code</label>
                        <input type="text" name="client_code" id="code" class="form-control" value="{{ $client->client_code }}" required>
                        @error('client_code')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="project_name" class="form-label">Project Name</label>
                        <input type="text" name="project_name" id="project_name" class="form-control" value="{{ $client->project_name }}" required>
                        @error('project_name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="summernote" class="form-control">{{ $client->description }}</textarea>
                        @error('description')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                               value="{{ \Carbon\Carbon::parse($client->start_date)->format('Y-m-d') }}">
                        @error('start_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    {{-- <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                               value="{{ \Carbon\Carbon::parse($client->end_date)->format('Y-m-d') }}">
                        @error('end_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div> --}}
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
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
                    <div class="mb-3">
                        <label for="budget" class="form-label">Budget</label>
                        <input type="number" name="budget" id="budget" class="form-control" step="0.01"
                            value="{{ $client->budget }}">
                        @error('budget')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Update Project</button>
                </form>
            </div>
        </div>
    </div>
@endsection
