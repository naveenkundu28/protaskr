<div class="dashboard_content_header">
    <div class="row">
        <div class="right_menu" id="rightMenu">
            <div class="search_box global-search">
                <button type="button" aria-label="Search"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </button>
                <?php $globalSearchPlaceholder = Auth::user()?->isAdmin() ? 'Search clients, users, projects, tasks...' : 'Search projects, tasks...'; ?>
                <input type="search" id="globalSearchInput" autocomplete="off" placeholder="{{ $globalSearchPlaceholder }}" aria-label="{{ $globalSearchPlaceholder }}">
                <div class="global-search-results" id="globalSearchResults" role="listbox" aria-label="Search results" hidden></div>
            </div>  
            <div class="right_profile_notification">
                @php($mentionNotifications = \App\Models\TaskMentionNotification::with(['task', 'sender'])->where('user_id', Auth::id())->latest()->limit(8)->get())
                @php($unreadMentionCount = \App\Models\TaskMentionNotification::where('user_id', Auth::id())->whereNull('read_at')->count())
                <div class="dropdown notification-dropdown">
                <button type="button" class="notification dropdown-toggle" id="mentionNotificationBell" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                    <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
                    </svg>
                    <em id="mentionNotificationCount" class="{{ $unreadMentionCount ? '' : 'd-none' }}">{{ $unreadMentionCount }}</em>
                </button>
                <ul class="dropdown-menu dropdown-menu-end notification-menu">
                    <li class="notification-menu-heading">
                        <strong>Notifications</strong>
                        <form id="markAllMentionsReadForm" method="POST" action="{{ route('notifications.mark-all-read') }}">
                            @csrf
                            <button type="submit" class="notification-mark-all">Mark all as read</button>
                        </form>
                    </li>
                    @forelse($mentionNotifications as $notice)
                        @php($senderName = $notice->sender?->name ?? 'Someone')
                        @php($senderInitials = collect(preg_split('/\s+/', trim($senderName)))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode(''))
                        <li>
                            <a class="notification-menu-item {{ $notice->read_at ? 'is-read' : 'is-unread' }}" href="{{ route('tasks.mentions.open', $notice) }}">
                                <span class="notification-menu-avatar avatar-tone-{{ $notice->mentioned_by % 5 }}" aria-hidden="true">{{ mb_strtoupper($senderInitials) }}</span>
                                <span class="notification-menu-body">
                                    <span class="notification-menu-copy"><strong>{{ $senderName }}</strong> mentioned you in a comment</span>
                                    <time>{{ $notice->created_at->diffForHumans() }}</time>
                                    <small>{{ $notice->message_excerpt }}</small>
                                    <span class="notification-menu-task"><i class="bi bi-calendar3" aria-hidden="true"></i>{{ $notice->task?->title ?? 'Task unavailable' }}</span>
                                </span>
                                @if(!$notice->read_at)<i class="notification-unread-dot" aria-label="Unread"></i>@endif
                            </a>
                        </li>
                    @empty<li class="notification-menu-empty">No notifications</li>@endforelse
                    <li class="notification-menu-footer"><a class="notification-see-all" href="{{ route('notifications.index') }}">See All Notifications <i class="bi bi-chevron-right" aria-hidden="true"></i></a></li>
                </ul></div>
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
@push('scripts')
<script>
    (function () {
        const count = document.getElementById('mentionNotificationCount');
        const bell = document.getElementById('mentionNotificationBell');
        const menu = bell?.closest('.notification-dropdown')?.querySelector('.notification-menu');
        const markAllForm = document.getElementById('markAllMentionsReadForm');
        const positionMenu = () => {
            if (!bell || !menu) return;
            const rect = bell.getBoundingClientRect();
            const width = Math.min(420, window.innerWidth - 24);
            const left = Math.max(12, Math.min(rect.right - width, window.innerWidth - width - 12));
            const menuHeight = menu.getBoundingClientRect().height;
            const maxTop = Math.max(12, window.innerHeight - menuHeight - 12);
            const belowTop = rect.bottom + 8;
            const top = belowTop <= maxTop ? belowTop : Math.max(12, Math.min(rect.top - menuHeight - 8, maxTop));
            menu.style.setProperty('position', 'fixed', 'important');
            menu.style.setProperty('width', width + 'px', 'important');
            menu.style.setProperty('max-width', 'calc(100vw - 24px)', 'important');
            menu.style.setProperty('left', left + 'px', 'important');
            menu.style.setProperty('right', 'auto', 'important');
            menu.style.setProperty('top', top + 'px', 'important');
            menu.style.setProperty('transform', 'none', 'important');
        };
        const dropdown = bell?.closest('.notification-dropdown');
        dropdown?.addEventListener('shown.bs.dropdown', () => requestAnimationFrame(positionMenu));
        bell?.addEventListener('click', () => { positionMenu(); window.setTimeout(positionMenu, 50); });
        window.addEventListener('resize', positionMenu);
        window.addEventListener('scroll', positionMenu, true);
        markAllForm?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = markAllForm.querySelector('button[type="submit"]');
            if (button) button.disabled = true;
            try {
                const response = await fetch(markAllForm.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(markAllForm)
                });
                if (!response.ok) throw new Error('Unable to update notifications');
                menu?.querySelectorAll('.notification-menu-item.is-unread').forEach((item) => {
                    item.classList.remove('is-unread');
                    item.classList.add('is-read');
                    item.querySelector('.notification-unread-dot')?.remove();
                });
                if (count) {
                    count.textContent = '0';
                    count.classList.add('d-none');
                }
            } catch (_) {
                window.location.reload();
            } finally {
                if (button) button.disabled = false;
            }
        });
        if (!count) return;
        const refresh = async () => {
            try {
                const response = await fetch('{{ route('notifications.unread-count') }}', { headers: { 'Accept': 'application/json' } });
                if (!response.ok) return;
                const data = await response.json();
                count.textContent = data.count;
                count.classList.toggle('d-none', Number(data.count) === 0);
            } catch (_) {}
        };
        window.setInterval(refresh, 15000);
    })();
</script>
@endpush

@push('scripts')
    <script>
        (() => {
            const input = document.getElementById('globalSearchInput');
            const results = document.getElementById('globalSearchResults');
            if (!input || !results) return;

            const endpoint = @json(route('search.global'));
            const icons = {
                Client: 'bi-building', User: 'bi-person', Lead: 'bi-person-lines-fill',
                Project: 'bi-kanban', Story: 'bi-bookmark', Task: 'bi-check2-square'
            };
            let debounceTimer;
            let activeRequest;

            const closeResults = () => {
                results.hidden = true;
                results.replaceChildren();
            };

            const showMessage = (message) => {
                const item = document.createElement('div');
                item.className = 'global-search-message';
                item.textContent = message;
                results.replaceChildren(item);
                results.hidden = false;
            };

            const renderResults = (items) => {
                results.replaceChildren();
                if (!items.length) {
                    showMessage('No matching records found.');
                    return;
                }

                items.forEach((item) => {
                    const link = document.createElement('a');
                    link.className = 'global-search-result';
                    link.href = item.url;
                    link.setAttribute('role', 'option');

                    const icon = document.createElement('span');
                    icon.className = 'global-search-result-icon';
                    const iconElement = document.createElement('i');
                    iconElement.className = `bi ${icons[item.type] || 'bi-search'}`;
                    icon.append(iconElement);

                    const copy = document.createElement('span');
                    copy.className = 'global-search-result-copy';
                    const title = document.createElement('span');
                    title.className = 'global-search-result-title';
                    title.textContent = item.title;
                    copy.append(title);
                    if (item.detail) {
                        const detail = document.createElement('span');
                        detail.className = 'global-search-result-detail';
                        detail.textContent = item.detail;
                        copy.append(detail);
                    }

                    const type = document.createElement('span');
                    type.className = 'global-search-result-type';
                    type.textContent = item.type;
                    link.append(icon, copy, type);
                    results.append(link);
                });
                results.hidden = false;
            };

            input.addEventListener('input', () => {
                window.clearTimeout(debounceTimer);
                if (activeRequest) activeRequest.abort();
                const term = input.value.trim();
                if (term.length < 2) {
                    closeResults();
                    return;
                }

                debounceTimer = window.setTimeout(async () => {
                    activeRequest = new AbortController();
                    showMessage('Searching…');
                    try {
                        const response = await fetch(`${endpoint}?q=${encodeURIComponent(term)}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            signal: activeRequest.signal
                        });
                        if (!response.ok) throw new Error('Search request failed');
                        const payload = await response.json();
                        renderResults(payload.results || []);
                    } catch (error) {
                        if (error.name !== 'AbortError') showMessage('Search is temporarily unavailable.');
                    }
                }, 220);
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeResults();
                if (event.key === 'ArrowDown' && !results.hidden) {
                    event.preventDefault();
                    results.querySelector('a')?.focus();
                }
            });
            document.addEventListener('click', (event) => {
                if (!event.target.closest('.global-search')) closeResults();
            });
        })();
    </script>
@endpush
