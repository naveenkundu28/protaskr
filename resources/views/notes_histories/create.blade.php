@extends('layouts.app')
@extends('layouts.header')

@section('content')
    <div class="container-fluid mt-80">
        <div style="padding: 0px 12px;">
            <div class="row">
                <div class="col-lg-12 d-flex justify-content-between mb-4 bg-white shadow-sm p-3 rounded">
                    <h2>Create Notes History</h2>
                    <div>
                        <a class="btn btn-primary btn-sm" href="{{ route('leads.notes-histories.index', $lead->id) }}">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-4 shadow-sm">
            <form method="POST" action="{{ route('leads.notes-histories.store', $lead->id) }}">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Lead</label>
                        <input type="text" class="form-control" value="{{ $lead->lead }}" disabled>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="lead_status_id" class="form-control">
                            <option value="">Select Status</option>
                            @foreach ($leadStatuses as $status)
                                <option value="{{ $status->id }}"
                                    {{ old('lead_status_id') == $status->id ? 'selected' : '' }}>
                                    {{ ucfirst($status->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Actions</label>
                        <input type="text" name="actions" class="form-control" value="{{ old('actions') }}">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Notes <span class="text-danger">*</span></label>
                        <textarea name="notes" class="form-control" rows="5">{{ old('notes') }}</textarea>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-floppy-disk"></i> Submit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
