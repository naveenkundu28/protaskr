@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-4 margin-tb">
                <div class="d-flex align-items-center justify-content-between shadow-sm p-3 rounded bg-white">
                    <div class="pull-left">
                        <h2>Create New User</h2>
                    </div>
                    <div class="pull-right">
                        <a class="btn btn-primary btn-sm" href="{{ route('users.index') }}"><i class="fa fa-arrow-left"></i>
                            Back</a>
                    </div>
                </div>
            </div>
        </div>

        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 p-4 mb-4 shadow-sm m-auto">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>User Type: <span class="text-danger">*</span></strong>
                            <select name="user_type" class="form-control mt-2" required>
                                <option value="">Select Type</option>
                                <option value="Internal" {{ old('user_type') == 'Internal' ? 'selected' : '' }}>Internal</option>
                                <option value="External" {{ old('user_type') == 'External' ? 'selected' : '' }}>External</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Employee ID: <span class="text-danger">*</span></strong>
                            <input value="{{ old('employee_id') }}" type="text" autocomplete="new-password" name="employee_id" placeholder="Employee ID" class="form-control mt-2" required>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Joining Date: <span class="text-danger">*</span></strong>
                            <input value="{{ old('joining_date') }}" type="date" name="joining_date" class="form-control mt-2" required>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Name: <span class="text-danger">*</span></strong>
                            <input value="{{ old('name') }}" type="text" name="name" placeholder="Name" class="form-control mt-2" required>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Email: <span class="text-danger">*</span></strong>
                            <input value="{{ old('email') }}" type="email" name="email" placeholder="Email" class="form-control mt-2" required>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Experience (Years): <span class="text-danger">*</span></strong>
                            <input value="{{ old('experience') }}" step="0.1" type="number" name="experience" placeholder="Experience (Years)" class="form-control mt-2" required>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Phone No: <span class="text-danger">*</span></strong>
                            <input value="{{ old('phone_no') }}" type="text" name="phone_no" placeholder="Phone No" class="form-control mt-2" required>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Alternate Phone No:</strong>
                            <input value="{{ old('alt_phone_no') }}" type="text" name="alt_phone_no" placeholder="Alternate Phone No" class="form-control mt-2">
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Password:<span class="text-danger">*</span></strong>
                            <input value="{{ old('password') }}" type="password" autocomplete="new-password" name="password" placeholder="Password" class="form-control mt-2">
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Confirm Password:</strong>
                            <input value="{{ old('confirm-password') }}" type="password" name="confirm-password" placeholder="Confirm Password" class="form-control mt-2">
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Primary Skills: <span class="text-danger">*</span></strong>
                            <select name="primary_skills[]" class="form-control select2 mt-2" required multiple>
                                @foreach($skills as $skill)
                                <option value="{{ $skill->id }}" {{ in_array($skill->id, old('primary_skills', [])) ? 'selected' : '' }}>
                                    {{ $skill->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Secondary Skills:</strong>
                            <select name="secondary_skills[]" class="form-control select2 mt-2" multiple>
                                @foreach($skills as $skill)
                                <option value="{{ $skill->id }}" {{ in_array($skill->id, old('secondary_skills', [])) ? 'selected' : '' }}>
                                    {{ $skill->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Tags: <span class="text-danger">*</span></strong>
                            <select name="tags[]" class="form-control select2 select2-mandatory mt-2" required>
                                @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Allow Manual Tracking:</strong>
                            <select name="manual" class="form-control select2 select2-mandatory mt-2">
                                <option selected value="1" {{ old('manual', 1) == 1 ? 'selected' : '' }}>
                                    Yes
                                </option>
                                <option selected value="0" {{ old('manual', 1) == 0 ? 'selected' : '' }}>
                                    No
                                </option>

                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-4">
                        <div class="form-group">
                            <strong>Role:</strong>
                            <select name="roles[]" class="form-control select2 select2-mandatory mt-2">
                                @foreach ($roles as $value => $label)
                                    <option value="{{ $value }}" 
                                    {{ in_array($value, old('roles', isset($roles) ? $roles : ['Employee'])) ? 'selected' : '' }}
                                    {{ in_array($value, isset($user) ? $user->roles->pluck('name')->toArray() : ['Employee']) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                        <button type="submit" class="btn btn-primary btn-sm mt-2 mb-3"><i class="fa-solid fa-floppy-disk"></i>
                            Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            function filterSkills() {
                let primary = $('select[name="primary_skills[]"]').val() || [];
                let secondary = $('select[name="secondary_skills[]"]').val() || [];

                $('select[name="primary_skills[]"] option').each(function() {

                    if (secondary.includes($(this).val())) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }

                });

                $('select[name="secondary_skills[]"] option').each(function() {

                    if (primary.includes($(this).val())) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }

                });

                $('select').trigger('change.select2');

            }

            $('select[name="primary_skills[]"]').on('change', filterSkills);
            $('select[name="secondary_skills[]"]').on('change', filterSkills);

            filterSkills();    
        });
    </script>
@endpush

