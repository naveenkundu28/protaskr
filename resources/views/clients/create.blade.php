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
                            <label for="employee" class="form-label">Employee</label>
                            <select name="employee[]" id="employee" class="form-select select2" required multiple>
                                {{-- <option value="">Select Employee</option> --}}
                                @foreach($employees as $employee)
                                <option value="{{$employee->id}}">{{$employee->name}}</option>
                                @endforeach
                            </select>
                            @error('employee')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3 col-sm-12 col-md-6 col-lg-4">
                            <label for="name" class="form-label">Client Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3 col-sm-12 col-md-6 col-lg-4">
                            <label for="email" class="form-label">Client Email</label>
                            <input type="email" name="client_email" id="email" class="form-control" required>
                            @error('client_email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3 col-sm-12 col-md-6">
                            <label for="code" class="form-label">Client Code</label>
                            <input type="text" name="client_code" id="code" class="form-control" required>
                            @error('client_code')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3 col-sm-12 col-md-12 col-lg-6">
                            <label for="project_name" class="form-label">Project Name</label>
                            <input type="text" name="project_name" id="project_name" class="form-control" required>
                            @error('project_name')
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
                        
                        <div class="mb-3 col-sm-12 col-md-6 col-lg-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control">
                            @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3 col-sm-12 col-md-6 col-lg-4">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="not_started">Not Started</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3 col-sm-12 col-md-12 col-lg-4">
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
