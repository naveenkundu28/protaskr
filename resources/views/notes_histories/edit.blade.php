@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div style="padding: 0px 12px;">
            <div class="row">
                <div class="col-lg-12 d-flex justify-content-between mb-4 bg-white shadow-sm p-3 rounded">
                    <h2>Edit Notes History</h2>
                    <div>
                        <a class="btn btn-primary btn-sm" href="{{ route('leads.notes-histories.index', $lead->id) }}">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card p-4 shadow-sm">
            <form method="POST" action="{{ route('leads.notes-histories.update', [$lead->id, $notesHistory->id]) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Lead</label>
                        <input type="text" class="form-control" value="{{ $lead->lead }}" disabled>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control">
                            <option value="email_inputs_shared" {{ old('status', $notesHistory->status) == 'email_inputs_shared' ? 'selected' : '' }}>Email inputs shared</option>
                            <option value="in_call_discussion" {{ old('status', $notesHistory->status) == 'in_call_discussion' ? 'selected' : '' }}>In call discussion</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Actions</label>
                        <input type="text" name="actions" class="form-control" value="{{ old('actions', $notesHistory->actions) }}">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Notes <span class="text-danger">*</span></label>
                        <textarea name="notes" class="form-control" rows="5">{{ old('notes', $notesHistory->notes) }}</textarea>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-floppy-disk"></i> Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
