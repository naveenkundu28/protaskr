@extends('layouts.app')

@section('content')
    @php
        $storedShiftStart = $user->shift_start_time ? \Carbon\Carbon::parse($user->shift_start_time)->format('H:i') : '';
        $storedShiftEnd = $user->shift_end_time ? \Carbon\Carbon::parse($user->shift_end_time)->format('H:i') : '';
        $shiftStartValue = old('shift_start_time', $storedShiftStart);
        $shiftEndValue = old('shift_end_time', $storedShiftEnd);
    @endphp

    <div class="profile-page container-fluid">
        <header class="profile-hero">
            <div class="profile-hero-copy">
                <span class="profile-eyebrow">ACCOUNT SETTINGS</span>
                <h1>Update profile</h1>
                <p>Manage your personal details, work schedule, and skills.</p>
            </div>
            <a class="profile-back" href="{{ route('dashboard') }}"><i class="bi bi-arrow-left"></i> Back to dashboard</a>
        </header>

        <form class="profile-form" method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <section class="profile-section">
                <div class="profile-section-heading">
                    <span class="profile-section-icon"><i class="bi bi-person-vcard"></i></span>
                    <div><h2>Personal information</h2><p>Your profile and contact details</p></div>
                </div>
                <div class="profile-fields">
                    <label class="profile-field">
                        <span>Full name <b>*</b></span>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="Your name" required>
                    </label>
                    <label class="profile-field">
                        <span>Joining date <b>*</b></span>
                        <input type="date" name="joining_date" value="{{ old('joining_date', $user->joining_date) }}" required>
                    </label>
                    <label class="profile-field">
                        <span>Email <b>*</b></span>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="name@example.com" required>
                    </label>
                    <label class="profile-field">
                        <span>Employee ID <b>*</b></span>
                        <input type="text" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" required>
                    </label>
                    <label class="profile-field">
                        <span>Experience (years) <b>*</b></span>
                        <input type="number" step="0.1" name="experience" value="{{ old('experience', $user->experience) }}" placeholder="e.g. 5.0" required>
                    </label>
                    <label class="profile-field">
                        <span>Phone number <b>*</b></span>
                        <input type="text" name="phone_no" value="{{ old('phone_no', $user->phone_no) }}" required>
                    </label>
                    <label class="profile-field">
                        <span>Alternate phone</span>
                        <input type="text" name="alt_phone_no" value="{{ old('alt_phone_no', $user->alt_phone_no) }}" placeholder="Optional">
                    </label>
                </div>
            </section>

            <section class="profile-section profile-shift-section">
                <div class="profile-section-heading">
                    <span class="profile-section-icon shift-icon"><i class="bi bi-clock-history"></i></span>
                    <div><h2>Work shift</h2><p>Optional · Your weekly timeline opens at the shift start and shows the next 12 hours.</p></div>
                </div>
                <div class="profile-shift-grid">
                    @foreach ([['start' => 'Shift start', 'name' => 'shift_start_time', 'value' => $shiftStartValue], ['start' => 'Shift end', 'name' => 'shift_end_time', 'value' => $shiftEndValue]] as $shift)
                        <label class="profile-shift-time">
                            <span>{{ $shift['start'] }}</span>
                            <div class="profile-select-wrap">
                                <select name="{{ $shift['name'] }}" aria-label="{{ $shift['start'] }}">
                                    <option value="">Select time</option>
                                    @foreach (range(0, 23) as $hour)
                                        @php
                                            $hourValue = sprintf('%02d:00', $hour);
                                            $hourLabel = \Carbon\Carbon::createFromTime($hour)->format('g:i A');
                                        @endphp
                                        <option value="{{ $hourValue }}" {{ $shift['value'] === $hourValue ? 'selected' : '' }}>{{ $hourLabel }}</option>
                                    @endforeach
                                </select>
                                <i class="bi bi-chevron-down" aria-hidden="true"></i>
                            </div>
                        </label>
                    @endforeach
                </div>
                <div class="profile-shift-note"><i class="bi bi-info-circle"></i> Choose whole hours. Shift end must be later than shift start. Leave both blank to use 9:00 AM.</div>
            </section>

            <section class="profile-section">
                <div class="profile-section-heading">
                    <span class="profile-section-icon security-icon"><i class="bi bi-shield-lock"></i></span>
                    <div><h2>Password</h2><p>Leave these blank to keep your current password.</p></div>
                </div>
                <div class="profile-fields profile-security-fields">
                    <label class="profile-field">
                        <span>New password</span>
                        <input autocomplete="new-password" type="password" name="password" placeholder="Enter a new password">
                    </label>
                    <label class="profile-field">
                        <span>Confirm new password</span>
                        <input type="password" name="confirm-password" placeholder="Confirm the new password">
                    </label>
                </div>
            </section>

            <section class="profile-section">
                <div class="profile-section-heading">
                    <span class="profile-section-icon skills-icon"><i class="bi bi-stars"></i></span>
                    <div><h2>Skills</h2><p>Choose your primary and secondary skills.</p></div>
                </div>
                <div class="profile-fields">
                    <label class="profile-field">
                        <span>Primary skills <b>*</b></span>
                        <select name="primary_skills[]" class="form-control select2" required multiple>
                            @foreach ($skills as $skill)
                                <option value="{{ $skill->id }}" {{ in_array($skill->id, old('primary_skills', $userPrimarySkills)) ? 'selected' : '' }}>{{ $skill->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="profile-field">
                        <span>Secondary skills</span>
                        <select name="secondary_skills[]" class="form-control select2" multiple>
                            @foreach ($skills as $skill)
                                <option value="{{ $skill->id }}" {{ in_array($skill->id, old('secondary_skills', $userSecondarySkills)) ? 'selected' : '' }}>{{ $skill->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <div class="profile-actions">
                <a href="{{ route('dashboard') }}" class="profile-cancel">Cancel</a>
                <button type="submit" class="profile-save"><i class="bi bi-check2"></i> Save profile</button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        .profile-page { max-width: 1500px; margin-top:80px; padding: 8px 20px 34px; color:#17324f; }
        .profile-hero { display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:18px; padding:22px 26px; border:1px solid #dce8f5; border-radius:14px; background:linear-gradient(115deg,#fff 0%,#f4f8ff 68%,#effaf6 100%); box-shadow:0 4px 14px rgba(31,65,110,.06); }
        .profile-eyebrow { color:#287cf0; font-size:10px; font-weight:800; letter-spacing:.11em; }
        .profile-hero h1 { margin:4px 0 3px; color:#142e4d; font-size:25px; font-weight:750; }
        .profile-hero p { margin:0; color:#70839b; font-size:12px; }
        .profile-back,.profile-cancel,.profile-save { display:inline-flex; align-items:center; justify-content:center; gap:7px; min-height:38px; border-radius:8px; padding:8px 13px; font-size:12px; font-weight:650; text-decoration:none; transition:.15s ease; }
        .profile-back { border:1px solid #d4e3f4; background:#fff; color:#286fc7; }
        .profile-back:hover,.profile-cancel:hover { background:#f2f7ff; color:#175fbb; }
        .profile-form { display:grid; gap:14px; }
        .profile-section { padding:20px 22px 22px; border:1px solid #e0e9f3; border-radius:13px; background:#fff; box-shadow:0 3px 11px rgba(31,65,110,.045); }
        .profile-section-heading { display:flex; align-items:center; gap:12px; margin-bottom:19px; padding-bottom:14px; border-bottom:1px solid #edf1f6; }
        .profile-section-icon { display:flex; align-items:center; justify-content:center; width:38px; height:38px; flex:0 0 38px; border-radius:11px; background:#eaf2ff; color:#287cf0; font-size:17px; }
        .profile-section-heading h2 { margin:0; color:#173653; font-size:15px; font-weight:750; }
        .profile-section-heading p { margin:3px 0 0; color:#8292a6; font-size:11px; }
        .profile-fields { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px 20px; }
        .profile-field { display:flex; flex-direction:column; gap:6px; min-width:0; color:#38536f; font-size:11px; font-weight:700; }
        .profile-field b { color:#e0515d; }
        .profile-field input,.profile-field select.form-control { width:100%; min-height:40px; border:1px solid #dce5ef; border-radius:8px; padding:8px 11px; background:#fbfcfe; color:#243e5a; font-size:12px; font-weight:450; box-shadow:none; }
        .profile-field input:focus,.profile-field select.form-control:focus,.profile-shift-time select:focus { border-color:#8ab8f5; outline:0; background:#fff; box-shadow:0 0 0 3px rgba(40,124,240,.11); }
        .profile-shift-section { border-color:#d8e7f7; background:linear-gradient(125deg,#f7faff,#fff 65%,#f6fbff); }
        .profile-shift-section .profile-section-heading { border-color:#e4edf7; }
        .shift-icon { background:#e7f1ff; color:#2477e8; }
        .profile-shift-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
        .profile-shift-time { display:flex; flex-direction:column; gap:8px; color:#38536f; font-size:11px; font-weight:700; }
        .profile-select-wrap { position:relative; }
        .profile-shift-time select { appearance:none; -webkit-appearance:none; width:100%; height:44px; border:1px solid #d5e1ef; border-radius:9px; padding:0 40px 0 13px; background:#fff; color:#1f3b59; font-size:13px; font-weight:600; cursor:pointer; box-shadow:0 1px 3px rgba(31,65,110,.035); }
        .profile-select-wrap i { position:absolute; top:50%; right:14px; transform:translateY(-50%); color:#6c829a; font-size:12px; pointer-events:none; }
        .profile-shift-note { display:flex; align-items:center; gap:7px; margin-top:13px; color:#71849a; font-size:11px; }
        .profile-shift-note i { color:#3981da; }
        .security-icon { background:#f1edff; color:#7b5ce1; }
        .skills-icon { background:#e7f8f1; color:#15966b; }
        .profile-security-fields { max-width:100%; }
        .profile-actions { display:flex; justify-content:flex-end; gap:9px; padding:2px 2px 0; }
        .profile-cancel { border:1px solid #d7e1ec; background:#fff; color:#637991; }
        .profile-save { border:1px solid #1769e8; background:#1769e8; color:#fff; cursor:pointer; }
        .profile-save:hover { border-color:#0f57c9; background:#0f57c9; }
        .profile-errors { margin-bottom:14px; border:1px solid #f1c5c9; border-radius:10px; padding:13px 16px; background:#fff6f6; color:#91434a; font-size:12px; }
        .profile-errors ul { padding-left:18px; }
        .profile-page .select2-container--default .select2-selection--multiple { min-height:40px; border:1px solid #dce5ef; border-radius:8px; background:#fbfcfe; }
        .profile-page .select2-container--default.select2-container--focus .select2-selection--multiple { border-color:#8ab8f5; box-shadow:0 0 0 3px rgba(40,124,240,.11); }
        @media (max-width:767.98px) {
            .profile-page { padding:6px 12px 24px; }
            .profile-hero { align-items:flex-start; flex-direction:column; padding:18px; }
            .profile-fields,.profile-shift-grid { grid-template-columns:1fr; }
            .profile-section { padding:16px; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.profile-page .select2').select2({
                placeholder: "Select skills",
                allowClear: true,
                width: '100%'
            });

            function filterSkills() {
                const primary = $('select[name="primary_skills[]"]').val() || [];
                const secondary = $('select[name="secondary_skills[]"]').val() || [];

                $('select[name="primary_skills[]"] option').each(function() {
                    $(this).prop('disabled', secondary.includes($(this).val()));
                });
                $('select[name="secondary_skills[]"] option').each(function() {
                    $(this).prop('disabled', primary.includes($(this).val()));
                });

                $('.profile-page .select2').trigger('change.select2');
            }

            $('select[name="primary_skills[]"], select[name="secondary_skills[]"]').on('change', filterSkills);
            filterSkills();
        });
    </script>
@endpush
