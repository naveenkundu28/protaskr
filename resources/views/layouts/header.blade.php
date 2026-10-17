<div class="dashboard_content_header">
    <div class="row">
        <div class="right_menu" id="rightMenu">
            <div class="search_box">
                <button><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </button>
                <input type="text" name="" placeholder="Search clients, users, skills, reports...">
            </div>  
            <div class="right_profile_notification">
                <button class="date-picker-button" type="button">
                    <!-- Calendar Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-date" viewBox="0 0 16 16">
                        <path d="M6.445 11.688V6.354h-.633A13 13 0 0 0 4.5 7.16v.695c.375-.257.969-.62 1.258-.777h.012v4.61zm1.188-1.305c.047.64.594 1.406 1.703 1.406 1.258 0 2-1.066 2-2.871 0-1.934-.781-2.668-1.953-2.668-.926 0-1.797.672-1.797 1.809 0 1.16.824 1.77 1.676 1.77.746 0 1.23-.376 1.383-.79h.027c-.004 1.316-.461 2.164-1.305 2.164-.664 0-1.008-.45-1.05-.82zm2.953-2.317c0 .696-.559 1.18-1.184 1.18-.601 0-1.144-.383-1.144-1.2 0-.823.582-1.21 1.168-1.21.633 0 1.16.398 1.16 1.23"/>
                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                    </svg>

                    <!-- Text Label -->
                    <span class="date-label">September 2026</span>

                    <!-- Chevron Down Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/>
                    </svg>
                </button>
                <span class="notification">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                    <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
                    </svg>
                    <em>2</em>
                </span>
                <div class="task-navbar-meta ms-lg-auto d-flex flex-column flex-lg-row align-items-lg-center gap-2 gap-lg-3">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="name_tag">AS</span> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-profile dropdown-menu-end"
                                aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Update
                                        Profile</a></li>
                                @canany(['skills-access', 'tags-access', 'hours-access', 'requirements-access', 'leads-status-access'])
                                    
                                    <li>
                                        <a class="dropdown-item d-flex justify-content-between align-items-center {{ request()->is('skills*') || request()->is('tags*') || request()->is('hours*') || request()->is('requirements*') || request()->is('lead-statuses*') }}"
                                            href="#mastersMenu" data-bs-toggle="collapse"
                                            aria-expanded="{{ request()->is('skills*') || request()->is('tags*') || request()->is('hours*') || request()->is('requirements*') || request()->is('lead-statuses*') }}"
                                            aria-controls="mastersMenu">
                                            <span>
                                                <i class="bi bi-folder me-2"></i> Masters
                                            </span>
                                            <i class="bi bi-chevron-down small"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <ul class="collapse list-unstyled mb-0 {{ request()->is('skills*') || request()->is('tags*') || request()->is('hours*') || request()->is('requirements*') || request()->is('lead-statuses*') }}"
                                            id="mastersMenu">
                                            @can('skills-access')
                                                <li>
                                                    <a class="dropdown-item ps-4 {{ request()->is('skills*') ? 'active' : '' }}"
                                                        href="{{ route('skills.index') }}">
                                                        <i class="bi bi-lightbulb me-2"></i> Skills
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('tags-access')
                                                <li>
                                                    <a class="dropdown-item ps-4 {{ request()->is('tags*') ? 'active' : '' }}"
                                                        href="{{ route('tags.index') }}">
                                                        <i class="bi bi-tags me-2"></i> Tags
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('hours-access')
                                                <li>
                                                    <a class="dropdown-item ps-4 {{ request()->is('hours*') ? 'active' : '' }}"
                                                        href="{{ route('hours.index') }}">
                                                        <i class="bi bi-clock me-2"></i> Hours
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('requirements-access')
                                                <li>
                                                    <a class="dropdown-item ps-4 {{ request()->is('requirements*') ? 'active' : '' }}"
                                                        href="{{ route('requirements.index') }}">
                                                        <i class="bi bi-list-task me-2"></i> Requirements
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('leads-status-access')
                                                    <li>
                                                        <a class="dropdown-item ps-4 {{ request()->is('lead-statuses*') ? 'active' : '' }}"
                                                            href="{{ route('lead-statuses.index') }}">
                                                            <i class="bi bi-info-circle me-2"></i> Lead Status
                                                        </a>
                                                    </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcanany

                                <li>
                                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    {{-- <span class="current-datetime" id="currentDateTime"></span> --}}
                </div>
            </div>
        </div>
    </div>
</div>