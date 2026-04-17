@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div style="padding: 0px 12px;">
            <div class="row">
                <div class="col-lg-12 d-flex justify-content-between mb-4 bg-white shadow-sm p-3 rounded">
                    <h2>Create Lead</h2>
                    <div>
                        <a class="btn btn-primary btn-sm" href="{{ route('leads.index') }}">
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
            <form id="leadForm" method="POST" action="{{ route('leads.store') }}">
                @csrf

                <div class="row">

                    <!-- Lead -->
                    <div class="col-md-4 mb-3">
                        <label>Lead <span class="text-danger">*</span></label>
                        <input type="text" name="lead" class="form-control" value="{{ old('lead') }}">
                    </div>

                    <!-- Status -->
                    <div class="col-md-4 mb-3">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control">
                            <option value="email_inputs_shared"
                                {{ old('status') == 'email_inputs_shared' ? 'selected' : '' }}>Email inputs shared</option>
                            <option value="in_call_discussion"
                                {{ old('status') == 'in_call_discussion' ? 'selected' : '' }}>In call discussion</option>
                        </select>
                    </div>

                    <!-- TL -->
                    <div class="col-md-4 mb-3">
                        <label>TL <span class="text-danger">*</span></label>
                        <select name="tl" class="form-control">
                            <option value="">Select</option>

                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ old('tl') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Team -->
                    <div class="col-md-4 mb-3">
                        <label>Team</label>
                        <select name="team" class="form-control">
                            <option value="1" {{ old('team') == 1 ? 'selected' : '' }}>Anshul</option>
                        </select>
                    </div>

                    <!-- Requirement -->
                    <div class="col-md-4 mb-3">
                        <label>Requirement <span class="text-danger">*</span></label>
                        <select name="requirement_id" class="form-control">
                            <option value="">Select</option>

                            @foreach ($requirements as $req)
                                <option value="{{ $req->id }}"
                                    {{ old('requirement_id') == $req->id ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', '-', $req->requirement)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Claimed Group -->
                    <div class="col-md-4 mb-3">
                        <label>Claimed Group</label>
                        <select name="claimed_group" class="form-control">
                            <option value="chat" {{ old('claimed_group') == 'chat' ? 'selected' : '' }}>Chat</option>
                            <option value="gl" {{ old('claimed_group') == 'gl' ? 'selected' : '' }}>GL</option>
                        </select>
                    </div>

                    <!-- Call Done -->
                    <div class="col-md-4 mb-3">
                        <label>Call Done</label>
                        <select name="call_done" class="form-control">
                            <option value="1" {{ old('call_done') == 1 ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ old('call_done') == 0 ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <!-- Cam Member -->
                    <div class="col-md-4 mb-3">
                        <label>Cam Member</label>
                        <input type="text" name="cam_member" class="form-control" value="{{ old('cam_member') }}">
                    </div>

                    <!-- Date -->
                    <div class="col-md-4 mb-3">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" value="{{ old('date') }}">
                    </div>

                    <!-- JD -->
                    <div class="col-md-12 mb-3">
                        <label>JD</label>
                        <textarea id="editor" name="jd" class="form-control rich-editor">{{ old('jd') }}</textarea>
                    </div>

                    <!-- Notes -->
                    <div class="col-md-12 mb-3">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
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
@push('scripts')
    <script>
        let config = {};
        config.toolbar = "basic";
        let editor = new RichTextEditor("#editor", config);

        $('#leadForm').on('submit', function() {
            $('#editor').val(editor.getHTMLCode());
        });
    </script>
@endpush
