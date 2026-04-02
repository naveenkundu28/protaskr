<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> @yield('title') | Task Manager </title>
    <link rel="shortcut icon" href="{{ asset('assets/img/logo-circle.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
</head>

<body>
    <div class="app-shell">
        <header class="topnav mb-4">
            <nav class="navbar navbar-expand-lg task-navbar">
                <div class="container-fluid">
                    <a class="navbar-brand task-logo" href="{{ route('dashboard') }}">
                        <img src="{{ asset('assets/img/logo-circle-horizontal.png') }}" class="img-fluid"
                            alt="task manager">
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNavDropdown">
                        <ul class="navbar-nav align-items-lg-center gap-lg-1">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('/') ? 'active' : '' }}"
                                    href="{{ route('dashboard') }}">
                                    <i class="bi bi-house-door"></i> Dashboard
                                </a>
                            </li>
                            @can('project-show')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('clients*') ? 'active' : '' }}"
                                        href="{{ route('clients.index') }}" role="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="bi bi-folder"></i> Clients
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item {{ request()->is('clients?type=archived*') ? 'active' : '' }}"
                                                href="{{ route('clients.index') }}">
                                                <i class="bi bi-list-ul me-2"></i> List Clients
                                            </a>
                                        </li>
                                        @can('user-show')
                                            <li>
                                                <a class="dropdown-item {{ request()->routeIs('services.listing') ? 'active' : '' }}"
                                                    href="{{ route('services.listing') }}">
                                                    <i class="bi bi-table me-2"></i> Service Listing
                                                </a>
                                            </li>
                                        @endcan
                                        
                                        {{-- <li>
                                            <a class="dropdown-item {{ request()->is('clients*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                                                <i class="bi bi-list-ul me-2"></i> Services List
                                            </a>
                                        </li> --}}
                                    </ul>
                                </li>
                            @endcan
                            @can('task-show')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('tasks*') ? 'active' : '' }}"
                                        href="{{ route('tasks.index') }}">
                                        <i class="bi bi-check2-square"></i> My Tasks
                                    </a>
                                </li>
                            @endcan
                            @can('user-show')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('users*') || request()->is('users/resigned*') ? 'active' : '' }}"
                                        href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-people"></i> Users
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item {{ request()->is('users') ? 'active' : '' }}"
                                                href="{{ route('users.index') }}">
                                                <i class="bi bi-people"></i> Users list
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ request()->is('users/resigned*') ? 'active' : '' }}"
                                                href="{{ route('resign.users.index') }}">
                                                <i class="bi bi-person-dash me-2"></i> Resigned Users
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endcan

                            @can('user-show')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('hourly-report*') || request()->is('daily-task-report*') ? 'active' : '' }}"
                                        href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-bar-chart"></i> Daily Report
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item {{ request()->is('hourly-report*') ? 'active' : '' }}"
                                                href="{{ route('daily.report') }}">
                                                <i class="bi bi-clock"></i> Hourly Report
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ request()->is('daily-task-report*') ? 'active' : '' }}"
                                                href="{{ route('daily.task.report') }}">
                                                <i class="bi bi-hourglass-split"></i> Daily Task Report
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reports*') ? 'active' : '' }}"
                                        href="{{ route('reports.index') }}">
                                        <i class="bi bi-file-earmark-bar-graph"></i> Reports
                                    </a>
                                </li>
                            @endcan
                        </ul>
                        <div
                            class="task-navbar-meta ms-lg-auto d-flex flex-column flex-lg-row align-items-lg-center gap-2 gap-lg-3">
                            <ul class="navbar-nav">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown"
                                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ Auth::user()->name }}
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-profile dropdown-menu-end"
                                        aria-labelledby="userDropdown">
                                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Update
                                                Profile</a></li>
                                        @if (auth()->user()->can('user-show') || auth()->user()->can('role-show'))
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item d-flex justify-content-between align-items-center {{ request()->is('skills*') || request()->is('tags*') || request()->is('year*') }}"
                                                    href="#mastersMenu" data-bs-toggle="collapse"
                                                    aria-expanded="{{ request()->is('skills*') || request()->is('tags*') || request()->is('year*') }}"
                                                    aria-controls="mastersMenu">
                                                    <span>
                                                        <i class="bi bi-folder me-2"></i> Masters
                                                    </span>
                                                    <i class="bi bi-chevron-down small"></i>
                                                </a>
                                            </li>
                                            <li>
                                                <ul class="collapse list-unstyled mb-0 {{ request()->is('skills*') || request()->is('tags*') || request()->is('year*') }}"
                                                    id="mastersMenu">
                                                    @can('user-show')
                                                        <li>
                                                            <a class="dropdown-item ps-4 {{ request()->is('skills*') ? 'active' : '' }}"
                                                                href="{{ route('skills.index') }}">
                                                                <i class="bi bi-lightbulb me-2"></i> Skills
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item ps-4 {{ request()->is('tags*') ? 'active' : '' }}"
                                                                href="{{ route('tags.index') }}">
                                                                <i class="bi bi-tags me-2"></i> Tags
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item ps-4 {{ request()->is('year*') ? 'active' : '' }}"
                                                                href="{{ route('master.hours.create') }}">
                                                                <i class="bi bi-clock me-2"></i> Hours
                                                            </a>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </li>
                                        @endif
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        @can('role-show')
                                            <li>
                                                <a class="dropdown-item ps-4 {{ request()->is('roles*') ? 'active' : '' }}"
                                                    href="{{ route('roles.index') }}">
                                                    <i class="bi bi-sticky me-2"></i> Roles
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                        @endcan

                                        <li>
                                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                                @csrf
                                                <button type="submit" class="dropdown-item">Logout</button>
                                            </form>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                            {{-- <span class="current-datetime" id="currentDateTime"></span> --}}
                        </div>
                    </div>
                </div>
            </nav>
        </header>
        <main>
            @yield('content')
        </main>
        <footer class="mt-auto py-3 text-center">
            <div class="container">
                <span class="text-muted">&copy; {{ date('Y') }} Task Manager | Developed by <a href="#"
                        target="_blank">VE</a> </span>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    @push('scripts')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                $('.select2').select2({
                    placeholder: "--Select--",
                    width: '100%',
                    allowClear: true
                });
                $('#userFilter').select2({
                    placeholder: "All Users",
                    width: '100%',
                    allowClear: true,
                });

                $('.select2-mandatory').select2({
                    allowClear: false
                });
                $(document).on('show.bs.dropdown', '.service-action-dropdown', function() {
                    const $dropdown = $(this);
                    const $menu = $dropdown.find('.dropdown-menu');
                    const offset = $dropdown.find('[data-bs-toggle="dropdown"]').offset();

                    $menu.appendTo('body').css({
                        position: 'absolute',
                        top: offset.top + $dropdown.outerHeight(),
                        left: offset.left,
                        zIndex: 1080
                    }).addClass('show');
                });

                $(document).on('hide.bs.dropdown', '.service-action-dropdown', function() {
                    const $dropdown = $(this);
                    const $menu = $('body > .dropdown-menu.show');

                    $menu.removeClass('show').removeAttr('style').appendTo($dropdown);
                });

                $('.auto-search-form').each(function() {
                    let timeoutId;
                    const form = this;
                    const $form = $(form);
                    const $input = $form.find('input[name="search"]');
                    const targetSelector = $form.data('search-target');

                    $form.on('submit', function(e) {
                        e.preventDefault();
                    });

                    $input.on('input', function() {
                        clearTimeout(timeoutId);
                        timeoutId = setTimeout(function() {
                            const queryString = new URLSearchParams(new FormData(form))
                                .toString();
                            const requestUrl = queryString ?
                                `${$form.attr('action')}?${queryString}` : $form.attr('action');

                            axios.get(requestUrl, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            }).then(function(response) {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(response.data,
                                    'text/html');
                                const newContent = doc.querySelector(targetSelector);
                                const currentContent = document.querySelector(
                                    targetSelector);

                                if (newContent && currentContent) {
                                    currentContent.innerHTML = newContent.innerHTML;
                                    window.history.replaceState({}, '', requestUrl);
                                }
                            }).catch(function(error) {
                                console.error('Search request failed', error);
                            });
                        }, 400);
                    });

                    $(document).on('click', `${targetSelector} .pagination a`, function(e) {
                        e.preventDefault();

                        axios.get($(this).attr('href'), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).then(function(response) {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(response.data, 'text/html');
                            const newContent = doc.querySelector(targetSelector);
                            const currentContent = document.querySelector(targetSelector);

                            if (newContent && currentContent) {
                                currentContent.innerHTML = newContent.innerHTML;
                                window.history.replaceState({}, '', $(e.currentTarget).attr(
                                    'href'));
                            }
                        }).catch(function(error) {
                            console.error('Pagination request failed', error);
                        });
                    });
                });
            });
        </script>
    @endpush
    @stack('scripts')

    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>


    <script>
        $('#summernote').summernote({
            placeholder: 'Description',
            tabsize: 2,
            height: 120,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', []]
            ]
        });
        /*        function updateDateTime() {
                    const now = new Date();
                    const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                    const day = dayNames[now.getDay()];
                    const date = now.toLocaleDateString(['en-US'], {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });
                    const time = now.toLocaleTimeString();

                    document.getElementById('currentDateTime').innerText = `${day}, ${date}  ${time}`;
                }

                updateDateTime();
                setInterval(updateDateTime, 1000);
                */
    </script>
</body>

</html>
