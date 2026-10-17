<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | ProTasker</title>
    <link rel="shortcut icon" href="{{ asset('assets/img/logo-circle.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --lg-blue: #0d6efd;
            --lg-blue-dark: #0955c7;
            --lg-ink: #13223f;
            --lg-text: #46536b;
            --lg-muted: #7a879c;
            --lg-line: #e6edf7;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Noto Sans", sans-serif;
            color: var(--lg-text);
            background: #f0f7fe;
        }

        .lg-shell {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
            min-height: 100vh;
        }

        /* ---- brand side (light dashboard theme) ---- */
        .lg-brand {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px 56px;
            overflow: hidden;
            color: var(--lg-ink);
            background:
                radial-gradient(circle at 14% 16%, rgba(13, 110, 253, .07), transparent 45%),
                radial-gradient(circle at 86% 85%, rgba(46, 130, 254, .08), transparent 50%),
                url("{{ asset('assets/img/side_bar.jpg') }}") center / cover no-repeat,
                linear-gradient(155deg, #f5f8fd 0%, #edf4fe 48%, #e1edfe 100%);
            border-right: 1px solid #e1ebf7;
        }

        .lg-brand::before,
        .lg-brand::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .lg-brand::before {
            width: 420px;
            height: 420px;
            top: -140px;
            right: -150px;
            background: radial-gradient(circle, rgba(13, 110, 253, .06), transparent 70%);
        }

        .lg-brand::after {
            width: 320px;
            height: 320px;
            bottom: -110px;
            left: -90px;
            background: radial-gradient(circle, rgba(46, 130, 254, .08), transparent 70%);
        }

        .lg-brand > * {
            position: relative;
            z-index: 1;
        }

        .lg-logo {
            display: inline-flex;
            align-self: flex-start;
            padding: 12px 20px;
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid #dce7f6;
            box-shadow: 0 8px 24px rgba(19, 34, 63, .07);
        }

        .lg-logo img {
            display: block;
            height: 34px;
            width: auto;
        }

        .lg-pitch h1 {
            margin: 0 0 14px;
            font-size: 2.6rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -.5px;
            color: var(--lg-ink);
        }

        .lg-pitch h1 .highlight {
            color: var(--lg-blue);
        }

        .lg-pitch p {
            max-width: 460px;
            margin: 0;
            font-size: 1.05rem;
            line-height: 1.6;
            color: var(--lg-text);
        }

        .lg-points {
            display: grid;
            gap: 14px;
            margin: 34px 0 0;
            padding: 0;
            list-style: none;
        }

        .lg-points li {
            display: flex;
            align-items: center;
            gap: 14px;
            font-weight: 600;
            color: #24334f;
        }

        .lg-points i {
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: #ffffff;
            color: var(--lg-blue);
            border: 1px solid #dbe6f5;
            box-shadow: 0 4px 12px rgba(13, 110, 253, .08);
            font-size: 1.1rem;
        }

        /* a small preview of the dashboard */
        .lg-preview {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .lg-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px 12px 12px;
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid #dce7f6;
            color: var(--lg-ink);
            box-shadow: 0 8px 22px rgba(19, 34, 63, .06);
        }

        .lg-chip .tile {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            font-size: 1.15rem;
        }

        .lg-chip .tile.blue {
            background: #e7f1ff;
            color: #0d6efd;
        }

        .lg-chip .tile.green {
            background: #e3faef;
            color: #059669;
        }

        .lg-chip .tile.orange {
            background: #fff2e5;
            color: #ea580c;
        }

        .lg-chip strong {
            display: block;
            font-size: 1.05rem;
            line-height: 1.15;
            color: var(--lg-ink);
        }

        .lg-chip span {
            font-size: .78rem;
            font-weight: 600;
            color: var(--lg-muted);
        }

        /* ---- form side ---- */
        .lg-form-side {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 24px;
        }

        .lg-card {
            width: 100%;
            max-width: 440px;
            padding: 40px 38px 34px;
            border: 1px solid #eef2f8;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 18px 50px rgba(31, 63, 122, .10);
        }

        .lg-mobile-logo {
            display: none;
            margin-bottom: 22px;
        }

        .lg-mobile-logo img {
            height: 30px;
            width: auto;
        }

        .lg-card h2 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            color: var(--lg-ink);
        }

        .lg-card .lg-sub {
            margin: 6px 0 26px;
            color: var(--lg-muted);
        }

        .lg-alert {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: 20px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: .92rem;
        }

        .lg-alert.is-error {
            background: #fdeaea;
            color: #b42318;
        }

        .lg-alert.is-ok {
            background: #e3f8ec;
            color: #157347;
        }

        .lg-field {
            margin-bottom: 18px;
        }

        .lg-field label {
            display: block;
            margin-bottom: 7px;
            font-size: .9rem;
            font-weight: 600;
            color: var(--lg-ink);
        }

        .lg-input {
            position: relative;
        }

        .lg-input > i.lead-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            font-size: 1.05rem;
            color: var(--lg-muted);
            pointer-events: none;
        }

        .lg-input input {
            width: 100%;
            height: 50px;
            padding: 0 16px 0 44px;
            border: 1px solid #d5deeb;
            border-radius: 12px;
            background: #f8faff;
            font-size: .98rem;
            color: var(--lg-ink);
            transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
        }

        .lg-input.has-toggle input {
            padding-right: 46px;
        }

        .lg-input input::placeholder {
            color: #a5b0c2;
        }

        .lg-input input:focus {
            outline: none;
            border-color: var(--lg-blue);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, .12);
        }

        .lg-input.has-error input {
            border-color: #ef4444;
        }

        .lg-input .toggle {
            position: absolute;
            top: 50%;
            right: 6px;
            width: 38px;
            height: 38px;
            transform: translateY(-50%);
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: var(--lg-muted);
            font-size: 1.1rem;
        }

        .lg-input .toggle:hover {
            background: #eef4ff;
            color: var(--lg-blue);
        }

        .lg-error {
            margin-top: 6px;
            font-size: .85rem;
            color: #dc2626;
        }

        .lg-submit {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            height: 52px;
            margin-top: 6px;
            border: 0;
            border-radius: 12px;
            background: var(--lg-blue);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            box-shadow: 0 10px 22px rgba(13, 110, 253, .28);
            transition: background-color .15s ease, transform .15s ease, box-shadow .15s ease;
        }

        .lg-submit:hover {
            background: var(--lg-blue-dark);
            transform: translateY(-1px);
        }

        .lg-submit:disabled {
            opacity: .75;
            transform: none;
            cursor: wait;
        }

        .lg-back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            margin-top: 16px;
            padding: 10px 0;
            font-size: .92rem;
            font-weight: 600;
            color: var(--lg-muted);
            text-decoration: none;
            transition: color .15s ease;
        }

        .lg-back-link:hover {
            color: var(--lg-blue);
        }

        .lg-foot {
            margin: 22px 0 0;
            text-align: center;
            font-size: .85rem;
            color: var(--lg-muted);
        }

        .lg-foot a {
            font-weight: 600;
            color: var(--lg-text);
            text-decoration: none;
        }

        @media (max-width: 991.98px) {
            .lg-shell {
                grid-template-columns: minmax(0, 1fr);
            }

            .lg-brand {
                display: none;
            }

            .lg-mobile-logo {
                display: block;
            }

            .lg-form-side {
                min-height: 100vh;
                background:
                    radial-gradient(circle at 15% 10%, rgba(13, 110, 253, .10), transparent 45%),
                    radial-gradient(circle at 90% 90%, rgba(124, 92, 255, .12), transparent 45%),
                    #f0f7fe;
            }
        }

        @media (max-width: 480px) {
            .lg-card {
                padding: 30px 22px 26px;
            }
        }
    </style>
</head>

<body>
    <div class="lg-shell">
        {{-- Brand side --}}
        <aside class="lg-brand">
            <a class="lg-logo" href="{{ route('login') }}">
                <img src="{{ asset('assets/img/new_logo.png') }}" alt="ProTasker">
            </a>

            <div class="lg-pitch">
                <h1>Plan better.<br>Track smarter.<br><span class="highlight">Deliver faster.</span></h1>
                <p>One place for your clients, projects, tasks and time, so the whole team always knows what's happening.</p>

                <ul class="lg-points">
                    <li><i class="bi bi-kanban-fill"></i> Projects and stories, organised by client</li>
                    <li><i class="bi bi-stopwatch-fill"></i> Track time on every task</li>
                    <li><i class="bi bi-bar-chart-line-fill"></i> Clear reports on progress and utilisation</li>
                </ul>
            </div>

            <div class="lg-preview" aria-hidden="true">
                <div class="lg-chip">
                    <span class="tile blue"><i class="bi bi-people-fill"></i></span>
                    <div><strong>Clients</strong><span>All in one view</span></div>
                </div>
                <div class="lg-chip">
                    <span class="tile green"><i class="bi bi-clock"></i></span>
                    <div><strong>Time tracking</strong><span>Start and stop</span></div>
                </div>
                <div class="lg-chip">
                    <span class="tile orange"><i class="bi bi-card-checklist"></i></span>
                    <div><strong>Tasks</strong><span>Never miss a deadline</span></div>
                </div>
            </div>
        </aside>

        {{-- Form side --}}
        <main class="lg-form-side">
            <div class="lg-card">
                <div class="lg-mobile-logo">
                    <img src="{{ asset('assets/img/new_logo.png') }}" alt="ProTasker">
                </div>

                <h2>Set new password</h2>
                <p class="lg-sub">Enter your email and choose a secure new password.</p>

                @if (session('status'))
                    <div class="lg-alert is-ok">
                        <i class="bi bi-check-circle-fill"></i>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif
                @if ($errors->any() && !$errors->has('email') && !$errors->has('password') && !$errors->has('password_confirmation'))
                    <div class="lg-alert is-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="lg-field">
                        <label for="email">Email</label>
                        <div class="lg-input {{ $errors->has('email') ? 'has-error' : '' }}">
                            <i class="bi bi-envelope lead-icon"></i>
                            <input type="email" name="email" id="email" value="{{ $email ?? old('email') }}"
                                placeholder="you@company.com" autocomplete="email" required autofocus>
                        </div>
                        @error('email')
                            <div class="lg-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="lg-field">
                        <label for="password">New Password</label>
                        <div class="lg-input has-toggle {{ $errors->has('password') ? 'has-error' : '' }}">
                            <i class="bi bi-lock lead-icon"></i>
                            <input type="password" name="password" id="password"
                                placeholder="Enter new password" autocomplete="new-password" required>
                            <button type="button" class="toggle" id="togglePassword" aria-label="Show password"
                                title="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="lg-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="lg-field">
                        <label for="password-confirm">Confirm Password</label>
                        <div class="lg-input has-toggle">
                            <i class="bi bi-shield-lock lead-icon"></i>
                            <input type="password" name="password_confirmation" id="password-confirm"
                                placeholder="Confirm new password" autocomplete="new-password" required>
                            <button type="button" class="toggle" id="toggleConfirmPassword" aria-label="Show password"
                                title="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="lg-submit" id="resetPasswordSubmit">
                        <span>Reset password</span> <i class="bi bi-check2-circle"></i>
                    </button>

                    <a href="{{ route('login') }}" class="lg-back-link">
                        <i class="bi bi-arrow-left"></i> <span>Back to sign in</span>
                    </a>
                </form>

                <p class="lg-foot">
                    &copy; {{ date('Y') }} ProTasker &middot; Developed by
                    <a href="https://www.virtualemployee.com/" target="_blank" rel="noopener">VE</a>
                </p>
            </div>
        </main>
    </div>

    <script>
        (function() {
            function wireToggle(inputId, toggleId) {
                const input = document.getElementById(inputId);
                const toggle = document.getElementById(toggleId);
                if (!input || !toggle) return;
                toggle.addEventListener('click', function() {
                    const show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    const icon = toggle.querySelector('i');
                    if (icon) icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
                    toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                    toggle.title = show ? 'Hide password' : 'Show password';
                    input.focus();
                });
            }

            wireToggle('password', 'togglePassword');
            wireToggle('password-confirm', 'toggleConfirmPassword');

            const form = document.getElementById('resetPasswordForm');
            const submit = document.getElementById('resetPasswordSubmit');
            form.addEventListener('submit', function() {
                submit.disabled = true;
                submit.querySelector('span').textContent = 'Resetting password…';
            });
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    submit.disabled = false;
                    submit.querySelector('span').textContent = 'Reset password';
                }
            });
        })();
    </script>
</body>

</html>
