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
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}?v={{ time() }}" />    
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />


    <link rel="stylesheet" href="{{ asset('richtexteditor/rte_theme_default.css') }}">
    <script src="{{ asset('richtexteditor/rte.js') }}"></script>
    <script src="{{ asset('richtexteditor/plugins/all_plugins.js') }}"></script>
    @stack('styles')
</head>

<body>
    <div class="app-shell">
        <script>if (window.matchMedia('(max-width: 1199.98px)').matches) document.currentScript.parentNode.classList.add('collapsed');</script>
        @include('layouts.header')
        <header class="topnav mb-4">
            <nav class="navbar navbar-expand-lg task-navbar">
                <div class="container-fluid">
                    <div class="top_logo_btn">
                        <a class="navbar-brand task-logo" href="{{ route('dashboard') }}">
                            <img src="{{ asset('assets/img/new_logo.png') }}" class="img-fluid full_logo"
                                alt="task manager">
                            <img src="{{ asset('assets/img/compress_logo.png') }}" class="img-fluid compress_logo"
                                alt="task manager">
                        </a>
                        <button class="menu_compress_btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
                            </svg>
                        </button>
                    </div>
                    <div class="collapse navbar-collapse" id="navbarNavDropdown">
                        <ul class="navbar-nav align-items-lg-center gap-lg-1">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('/') ? 'active' : '' }}"
                                    href="{{ route('dashboard') }}">
                                    <i class="bi bi-house-door-fill"></i> Dashboard
                                </a>
                            </li>
                            @can('projects-access')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('projects*') || request()->is('stories*') ? 'active' : '' }}"
                                        href="{{ route('projects.index') }}">
                                        <i class="bi bi-kanban"></i> Projects
                                    </a>
                                </li>
                                @endcan
                                @can('clients-access')
                                @php($clientsMenuOpen = request()->is('clients*') || request()->routeIs('services.listing.index', 'services.bulk.edit'))
                                <li class="nav-item sidebar-nav-item has-submenu {{ $clientsMenuOpen ? 'is-open is-active-route' : '' }}">
                                    <div class="sidebar-parent-row">
                                        <a class="nav-link sidebar-parent-link {{ $clientsMenuOpen ? 'active' : '' }}" href="{{ route('clients.index') }}">
                                            <i class="bi bi-building"></i><span class="sidebar-nav-label">Clients</span>
                                        </a>
                                        <button class="sidebar-submenu-toggle" type="button" aria-label="Toggle Clients submenu" aria-controls="clientsSubmenu" aria-expanded="{{ $clientsMenuOpen ? 'true' : 'false' }}">
                                            <i class="bi {{ $clientsMenuOpen ? 'bi-dash-lg' : 'bi-plus-lg' }}" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <ul class="dropdown-menu sidebar-submenu {{ $clientsMenuOpen ? 'show' : '' }}" id="clientsSubmenu">
                                        <li>
                                            <a class="dropdown-item {{ request()->is('clients?type=archived*') ? 'active' : '' }}"
                                                href="{{ route('clients.index') }}">
                                                <i class="bi bi-list-ul me-2"></i> List Clients
                                            </a>
                                        </li>
                                        @can('services-listing')
                                            <li>
                                                <a class="dropdown-item {{ request()->routeIs('services.listing.index') ? 'active' : '' }}"
                                                    href="{{ route('services.listing.index') }}">
                                                    <i class="bi bi-table me-2"></i> Service Listing
                                                </a>
                                            </li>
                                        @endcan
                                        @can('services-bulk-access')
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('services.bulk.edit') ? 'active' : '' }}"
                                                href="{{ route('services.bulk.edit') }}">
                                                <i class="bi bi-plus-square me-2"></i> Bulk Services
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

                            @can('leads-access')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('leads*') ? 'active' : '' }}"
                                        href="{{ route('leads.index') }}">
                                        <i class="bi bi-people"></i> My Leads
                                    </a>
                                </li>
                            @endcan
                            @can('tasks-access')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('tasks*') ? 'active' : '' }}"
                                        href="{{ route('tasks.index') }}">
                                        <i class="bi bi-check2-square"></i> My Tasks
                                    </a>
                                </li>
                            @endcan
                            @can('users-access')
                                @php($usersMenuOpen = request()->is('users*'))
                                <li class="nav-item sidebar-nav-item has-submenu {{ $usersMenuOpen ? 'is-open is-active-route' : '' }}">
                                    <div class="sidebar-parent-row">
                                        <a class="nav-link sidebar-parent-link {{ $usersMenuOpen ? 'active' : '' }}" href="{{ route('users.index') }}">
                                            <i class="bi bi-people"></i><span class="sidebar-nav-label">Users</span>
                                        </a>
                                        <button class="sidebar-submenu-toggle" type="button" aria-label="Toggle Users submenu" aria-controls="usersSubmenu" aria-expanded="{{ $usersMenuOpen ? 'true' : 'false' }}">
                                            <i class="bi {{ $usersMenuOpen ? 'bi-dash-lg' : 'bi-plus-lg' }}" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <ul class="dropdown-menu sidebar-submenu {{ $usersMenuOpen ? 'show' : '' }}" id="usersSubmenu">
                                        <li>
                                            <a class="dropdown-item {{ request()->is('users') ? 'active' : '' }}"
                                                href="{{ route('users.index') }}">
                                                <i class="bi bi-people me-2"></i> Users list
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ request()->is('users/resigned*') ? 'active' : '' }}"
                                                href="{{ route('users.resigned') }}">
                                                <i class="bi bi-person-dash me-2"></i> Resigned Users
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endcan

                            @canany(['reports-hourly', 'reports-daily-task'])
                                @php($reportsMenuOpen = request()->is('reports/hourly*') || request()->is('reports/daily-task*'))
                                <li class="nav-item sidebar-nav-item has-submenu {{ $reportsMenuOpen ? 'is-open is-active-route' : '' }}">
                                    <div class="sidebar-parent-row">
                                        <a class="nav-link sidebar-parent-link {{ $reportsMenuOpen ? 'active' : '' }}" href="{{ Auth::user()->can('reports-hourly') ? route('reports.hourly.index') : route('reports.daily-task.index') }}">
                                            <i class="bi bi-bar-chart-line"></i><span class="sidebar-nav-label">Daily Report</span>
                                        </a>
                                        <button class="sidebar-submenu-toggle" type="button" aria-label="Toggle Daily Report submenu" aria-controls="reportsSubmenu" aria-expanded="{{ $reportsMenuOpen ? 'true' : 'false' }}">
                                            <i class="bi {{ $reportsMenuOpen ? 'bi-dash-lg' : 'bi-plus-lg' }}" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <ul class="dropdown-menu sidebar-submenu {{ $reportsMenuOpen ? 'show' : '' }}" id="reportsSubmenu">
                                        @can('reports-hourly')
                                        <li>
                                            <a class="dropdown-item {{ request()->is('reports/hourly*') ? 'active' : '' }}"
                                                href="{{ route('reports.hourly.index') }}">
                                                <i class="bi bi-clock me-2"></i> Hourly Report
                                            </a>
                                        </li>
                                        @endcan
                                        @can('reports-daily-task')
                                        <li>
                                            <a class="dropdown-item {{ request()->is('reports/daily-task*') ? 'active' : '' }}"
                                                href="{{ route('reports.daily-task.index') }}">
                                                <i class="bi bi-hourglass-split me-2"></i> Task Reports
                                            </a>
                                        </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcanany

                            @can('reports-access')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reports') ? 'active' : '' }}"
                                        href="{{ route('reports.index') }}">
                                        <i class="bi bi-file-earmark-ppt"></i> Reports
                                    </a>
                                </li>
                            @endcan
                        </ul>
                        <div class="ve_tracker_version">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.3.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M372.2 116C372.2 136.9 359.8 155 342 163.2L448 256L552.4 235.1C547.1 227.4 544 218 544 208C544 181.5 565.5 160 592 160C618.5 160 640 181.5 640 208C640 234 619.4 255.1 593.6 256L481 506.3C470.7 529.3 447.8 544 422.6 544L217.4 544C192.2 544 169.4 529.2 159 506.3L46.4 256C20.6 255.1 0 234 0 208C0 181.5 21.5 160 48 160C74.5 160 96 181.5 96 208C96 218.1 92.9 227.4 87.6 235.1L192 256L298.1 163.1C280.4 154.8 268.1 136.8 268.1 116C268.1 87.3 291.4 64 320.1 64C348.8 64 372.1 87.3 372.1 116z"/></svg>
                            <span>
                                <h3>Pro Taskr Tracker</h3>
                                <p>v1.0.0</p>
                            </span>
                        </div>
                    </div>
                </div>
            </nav>
        </header>
        <main>
            @yield('content')
            {{-- <footer class="mt-auto py-3 text-center">
            <div class="container">
                <span class="text-muted">&copy; {{ date('Y') }} Task Manager | Developed by <a href="#"
                        target="_blank">VE</a> </span>
            </div>
        </footer> --}}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('assets/script.js') }}"></script>
    @push('scripts')
        <script>
            $(document).ready(function() {
                $('[data-bs-toggle="tooltip"]').tooltip();
                $('.select2').select2({
                    placeholder: "--Select--",
                    width: '100%',
                    allowClear: true
                });

                $('.select2-tags').select2({
                    placeholder: "Type or select skills",
                    width: '100%',
                    tags: true,
                    tokenSeparators: [','],
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
                    const $trigger = $dropdown.find('[data-bs-toggle="dropdown"]');
                    const offset = $trigger.offset();

                    $menu.appendTo('body').css({
                        position: 'absolute',
                        top: offset.top + $trigger.outerHeight(),
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
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>

    <script>
        window.initSummernoteEditors = function() {
            if (typeof $.fn.summernote !== 'function') return;

            $('.summernote, #summernote').each(function() {
                var $el = $(this);
                if (!$el.next('.note-editor').length) {
                    $el.summernote({
                        placeholder: $el.attr('placeholder') || 'Enter description...',
                        tabsize: 2,
                        height: 150,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview']]
                        ]
                    });
                }
            });
        };

        $(document).ready(function() {
            window.initSummernoteEditors();

            // Auto-sync summernote content into textarea before form submission
            $(document).on('submit', 'form', function() {
                $(this).find('.summernote, #summernote').each(function() {
                    if ($(this).next('.note-editor').length) {
                        $(this).val($(this).summernote('code'));
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
