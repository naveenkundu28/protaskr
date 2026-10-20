@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/project-module.css') }}">
@endpush
@section('content')
    <div class="container-fluid mt-80 users-page users-form-page">
        <div class="pm-page pm-form-wrap users-form-wrap">
            <div class="pm-card pm-form users-form-card">
                <div class="pm-form-title users-form-title">
                    <div>
                        <p class="pm-eyebrow">TEAM DIRECTORY</p>
                        <h2>Create User</h2>
                        <p class="users-form-subtitle">Add a team member and set up their profile.</p>
                    </div>
                    <a class="btn users-form-back" href="{{ route('users.index') }}"><i class="bi bi-arrow-left"></i> Back to users</a>
                </div>

        @if (count($errors) > 0)
            <div class="alert alert-danger users-form-error">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

            <form method="POST" action="{{ route('users.store') }}" class="users-form" novalidate>
                @csrf
                <div class="row g-3 users-form-grid">
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <div class="form-group">
                            <strong>User Type: <span class="text-danger">*</span></strong>
                            <select name="user_type" class="form-control mt-2" required>
                                <option value="">Select Type</option>
                                <option value="Internal" {{ old('user_type') == 'Internal' ? 'selected' : '' }}>Internal
                                </option>
                                <option value="External" {{ old('user_type') == 'External' ? 'selected' : '' }}>External
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <div class="form-group">
                            <strong>Employee ID: <span class="text-danger">*</span></strong>
                            <input value="{{ old('employee_id') }}" type="text" autocomplete="new-password"
                                name="employee_id" placeholder="Employee ID" class="form-control mt-2" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3 user-internal-only">
                        <div class="form-group">
                            <strong>Joining Date: <span class="text-danger">*</span></strong>
                            <input value="{{ old('joining_date') }}" type="date" name="joining_date"
                                class="form-control mt-2" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <div class="form-group">
                            <strong>Name: <span class="text-danger">*</span></strong>
                            <input value="{{ old('name') }}" type="text" name="name" placeholder="Name"
                                class="form-control mt-2" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3 user-internal-only">
                        <div class="form-group">
                            <strong>Email: <span class="text-danger">*</span></strong>
                            <input value="{{ old('email') }}" type="email" name="email" placeholder="Email"
                                class="form-control mt-2" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3 user-internal-only">
                        <div class="form-group">
                            <strong>Experience (Years): <span class="text-danger">*</span></strong>
                            <input value="{{ old('experience') }}" step="0.1" type="number" name="experience"
                                placeholder="Experience (Years)" class="form-control mt-2" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3 user-internal-only">
                        <div class="form-group">
                            <strong>Phone No: <span class="text-danger">*</span></strong>
                            <input value="{{ old('phone_no') }}" type="text" name="phone_no" placeholder="Phone No"
                                class="form-control mt-2" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3 user-internal-only">
                        <div class="form-group">
                            <strong>Alternate Phone No:</strong>
                            <input value="{{ old('alt_phone_no') }}" type="text" name="alt_phone_no"
                                placeholder="Alternate Phone No" class="form-control mt-2">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3 user-internal-only">
                        <div class="form-group">
                            <strong>Password:<span class="text-danger">*</span></strong>
                            <input value="{{ old('password') }}" type="password" autocomplete="new-password"
                                name="password" placeholder="Password" class="form-control mt-2" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3 user-internal-only">
                        <div class="form-group">
                            <strong>Confirm Password:</strong>
                            <input value="{{ old('confirm-password') }}" type="password" name="confirm-password"
                                placeholder="Confirm Password" class="form-control mt-2">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3 user-external-required">
                        <div class="form-group">
                            <strong>Primary Skills: <span class="text-danger">*</span></strong>
                            <select name="primary_skills[]" class="form-control select2 mt-2" required multiple>
                                @foreach ($skills as $skill)
                                    <option value="{{ $skill->id }}"
                                        {{ in_array($skill->id, old('primary_skills', [])) ? 'selected' : '' }}>
                                        {{ $skill->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3 mb-3 user-internal-only">
                        <div class="form-group">
                            <strong>Secondary Skills:</strong>
                            <select name="secondary_skills[]" class="form-control select2 mt-2" multiple>
                                @foreach ($skills as $skill)
                                    <option value="{{ $skill->id }}"
                                        {{ in_array($skill->id, old('secondary_skills', [])) ? 'selected' : '' }}>
                                        {{ $skill->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3 mb-3 user-external-required">
                        <div class="form-group">
                            <strong>Tags: <span class="text-danger">*</span></strong>
                            <select name="tags[]" class="form-control select2 select2-mandatory mt-2" required>
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}"
                                        {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3 mb-4 user-internal-only">
                        <div class="form-group">
                            <strong>Role: <span class="text-danger">*</span></strong>
                            <select name="role_id" class="form-control select2 select2-mandatory mt-2" required>
                                @foreach ($roles as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('role_id', array_search(\App\Support\Roles::RESOURCE, $roles)) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-4 user-internal-only">
                        <div class="form-group">
                            <strong>Show Report:</strong>
                            <select name="is_show_report" class="form-control select2 mt-2">
                                <option value="1" {{ old('is_show_report', 1) == 1 ? 'selected' : '' }}>
                                    Yes
                                </option>
                                <option value="0" {{ old('is_show_report', 1) == 0 ? 'selected' : '' }}>
                                    No
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="pm-actions users-form-actions">
                            <a class="btn btn-cancel" href="{{ route('users.index') }}">Cancel</a>
                            <button type="submit" class="btn btn-primary pm-button"><i class="fa-solid fa-floppy-disk"></i> Create User</button>
                        </div>
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

            function toggleFieldsForUserType() {
                const isExternal = $('select[name="user_type"]').val() === 'External';

                $('.user-internal-only').toggleClass('d-none', isExternal).find(':input').each(function() {
                    const $field = $(this);
                    if ($field.data('internal-required') === undefined) {
                        $field.data('internal-required', this.required);
                    }
                    const isRequired = !isExternal && $field.data('internal-required');
                    $field.prop('disabled', isExternal);
                    if ($field.is('select.select2, select.select2-mandatory')) {
                        $field.prop('required', false).attr('data-select-required', isRequired ? 'true' : 'false');
                    } else {
                        $field.prop('required', isRequired);
                    }
                });

                $('.user-external-required :input').each(function() {
                    const $field = $(this);
                    if ($field.data('internal-required') === undefined) {
                        $field.data('internal-required', this.required);
                    }
                    const isRequired = isExternal || $field.data('internal-required');
                    if ($field.is('select.select2, select.select2-mandatory')) {
                        $field.prop('required', false).attr('data-select-required', isRequired ? 'true' : 'false');
                    } else {
                        $field.prop('required', isRequired);
                    }
                });

                $('.user-external-required select').trigger('change.select2');
            }

            $('.users-form').on('submit', function(event) {
                const $requiredSelects = $(this).find('select[data-select-required="true"]:enabled');
                let $firstInvalid = null;

                $requiredSelects.each(function() {
                    const value = $(this).val();
                    if (!value || (Array.isArray(value) && value.length === 0)) {
                        $firstInvalid = $(this);
                        return false;
                    }
                });

                $('.user-select-validation').remove();
                $('.select2-container').removeClass('is-invalid');
                if ($firstInvalid) {
                    event.preventDefault();
                    const $container = $firstInvalid.next('.select2-container');
                    $container.addClass('is-invalid');
                    $('<div class="invalid-feedback user-select-validation d-block">Please select at least one option.</div>')
                        .insertAfter($container);
                    $firstInvalid.select2('open');
                    return;
                }

                if (!this.checkValidity()) {
                    event.preventDefault();
                    this.reportValidity();
                }
            });

            $('select[name="user_type"]').on('change', toggleFieldsForUserType);
            toggleFieldsForUserType();

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
