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
    <link rel="stylesheet" href="{{asset('assets/style.css')}}" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
</head>

<body>
    <div class="sidebar d-flex flex-column p-3">
        <h4 class="mb-4 text-center">
            <a href="{{ route('dashboard') }}">
                <img style="filter: invert(100%) brightness(200%);"
                    src="{{ asset('assets/img/logo-circle-horizontal.png') }}" class="img-fluid" width="100%"
                    alt="task manager">
            </a>
        </h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-house-door"></i> Home
                </a>
            </li>
            @can('project-show')
                <li class="nav-item">
                    <div class="accordion" id="projectsAccordion">
                        <div class="accordion-item position-relative">
                            <h6 class="accordion-header d-flex justify-content-between align-items-center"
                                id="headingProjects">
                                <!-- Projects text with link -->
                                <a class="nav-link d-flex align-items-center w-100 {{ request()->is('projects*') ? 'active' : '' }}"
                                    href="{{ route('clients.index') }}">
                                    <i class="bi bi-folder"></i> Clients
                                </a>

                                <!-- Arrow button for dropdown -->
                                <button
                                    class="text-white accordion-button collapsed border-0 bg-transparent shadow-none p-0 position-absolute end-0 w-25"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#collapseProjects"
                                    aria-expanded="false" aria-controls="collapseProjects">
                                </button>
                            </h6>

                            <div id="collapseProjects" class="accordion-collapse collapse" aria-labelledby="headingProjects"
                                data-bs-parent="#projectsAccordion">
                                <div class="accordion-body">
                                    <ul class="list-group custom-h">
                                        <li class="list-group-item f-14">
                                            <a class="nav-link"
                                                href="{{ route('clients.index') }}">
                                                List Clients

                                            </a>
                                        </li>
                                        {{-- @foreach ($projectsDropdown as $item)
                                            <li class="list-group-item f-14">
                                                <a class="nav-link"
                                                    href="{{ route('tasks.index', ['project_id' => $item->id]) }}">
                                                    {{ $item->name }} - {{ '(' . ($item->to_do_tasks + $item->in_progress_tasks + $item->in_review_tasks) . ')' }}

                                                </a>
                                            </li>
                                        @endforeach --}}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>


            @endcan
            @can('task-show')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('tasks*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">
                        <i class="bi bi-check2-square"></i> My Tasks
                    </a>
                </li>
            @endcan
            @can('user-show')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('users*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <i class="bi bi-people"></i> Users
                    </a>
                </li>
            @endcan
            @can('user-show')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('skills*') ? 'active' : '' }}" href="{{ route('skills.index') }}">
                        <i class="bi bi-people"></i> Master Skills
                    </a>
                </li>
            @endcan
            @can('role-show')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('roles*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                        <i class="bi bi-sticky"></i> Roles
                    </a>
                </li>
            @endcan
            @can('project-show')
                @if (Auth::user()->hasRole('Admin'))
                    <li class="nav-item">
                        <a class="nav-link {{ request('type') == 'archived' ? 'active' : '' }}"
                            href="{{ route('clients.index', ['type' => 'archived']) }}">
                            <i class="bi bi-sticky"></i> Archived Projects
                        </a>
                    </li>
                @endif
            @endcan
        </ul>
    </div>

    <div class="content d-flex flex-column">
        <header class="topnav mb-4">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <a class="navbar-brand" href="{{ route('dashboard') }}">
                        <span class="fw-normal" id="currentDateTime"></span>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                        <ul class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="#">Settings</a></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                            @csrf
                                            <button type="submit" class="dropdown-item">Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
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
    @push('scripts')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                $('.select2').select2({
                    placeholder: "--Select--",
                    allowClear: true
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
        function updateDateTime() {
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
    </script>
</body>

</html>
