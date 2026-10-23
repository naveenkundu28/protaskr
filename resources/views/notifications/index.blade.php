@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<div class="container-fluid mt-80 notifications-page" id="notificationsPage"
    data-endpoint="{{ route('notifications.index') }}"
    data-mark-all-endpoint="{{ route('notifications.mark-all-read') }}">
    <div class="notifications-page-heading">
        <div>
            <h1>Notifications</h1>
            <p>Stay updated with your mentions and important task activity.</p>
        </div>
        <form id="notificationsMarkAllForm" method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="notifications-mark-all"><i class="bi bi-check2" aria-hidden="true"></i> Mark All as Read</button>
        </form>
    </div>

    <section class="notifications-list-card">
        <div class="notifications-list-tools">
            <label class="notifications-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input id="notificationsSearch" type="search" value="{{ request('search') }}" placeholder="Search notifications..." autocomplete="off" aria-label="Search notifications">
            </label>
            <label class="notifications-date-filter">
                <i class="bi bi-calendar3" aria-hidden="true"></i>
                <select id="notificationsDateRange" aria-label="Filter notifications by date">
                    <option value="all" @selected(request('date_range', 'all') === 'all')>All Time</option>
                    <option value="today" @selected(request('date_range') === 'today')>Today</option>
                    <option value="last_week" @selected(request('date_range') === 'last_week')>Last Week</option>
                    <option value="last_month" @selected(request('date_range') === 'last_month')>Last Month</option>
                </select>
                <i class="bi bi-chevron-down notifications-date-chevron" aria-hidden="true"></i>
            </label>
        </div>

        <div id="notificationsResults" aria-live="polite">
            @include('notifications._results', ['notifications' => $notifications])
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const page = document.getElementById('notificationsPage');
    if (!page) return;
    const endpoint = page.dataset.endpoint;
    const searchInput = document.getElementById('notificationsSearch');
    const dateSelect = document.getElementById('notificationsDateRange');
    const results = document.getElementById('notificationsResults');
    const markAllForm = document.getElementById('notificationsMarkAllForm');
    let timer;
    let activeRequest;

    const makeUrl = (pageNumber = 1) => {
        const url = new URL(endpoint, window.location.origin);
        if (searchInput.value.trim()) url.searchParams.set('search', searchInput.value.trim());
        if (dateSelect.value !== 'all') url.searchParams.set('date_range', dateSelect.value);
        if (pageNumber > 1) url.searchParams.set('page', pageNumber);
        return url;
    };

    const loadResults = async (url, replaceHistory = true) => {
        activeRequest?.abort();
        activeRequest = new AbortController();
        results.classList.add('is-loading');
        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: activeRequest.signal
            });
            if (!response.ok) throw new Error('Could not load notifications');
            const data = await response.json();
            results.innerHTML = data.html;
            if (replaceHistory) window.history.replaceState({}, '', url);
        } catch (error) {
            if (error.name !== 'AbortError') window.location.assign(url);
        } finally {
            results.classList.remove('is-loading');
        }
    };

    const applyFilters = () => loadResults(makeUrl(1));
    searchInput.addEventListener('input', () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(applyFilters, 250);
    });
    dateSelect.addEventListener('change', applyFilters);
    results.addEventListener('click', (event) => {
        const link = event.target.closest('.notifications-pagination a');
        if (!link) return;
        event.preventDefault();
        const requestedUrl = new URL(link.href, window.location.origin);
        const pageNumber = Number(requestedUrl.searchParams.get('page') || 1);
        loadResults(makeUrl(pageNumber));
    });
    window.addEventListener('popstate', () => {
        const url = new URL(window.location.href);
        searchInput.value = url.searchParams.get('search') || '';
        dateSelect.value = url.searchParams.get('date_range') || 'all';
        loadResults(url, false);
    });

    markAllForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = markAllForm.querySelector('button');
        button.disabled = true;
        try {
            const response = await fetch(markAllForm.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(markAllForm)
            });
            if (!response.ok) throw new Error('Could not update notifications');
            document.querySelectorAll('.notification-page-item.is-unread').forEach((item) => {
                item.classList.remove('is-unread');
                item.classList.add('is-read');
                item.querySelector('.notification-unread-dot')?.remove();
            });
            document.querySelectorAll('.notification-menu-item.is-unread').forEach((item) => {
                item.classList.remove('is-unread');
                item.classList.add('is-read');
                item.querySelector('.notification-unread-dot')?.remove();
            });
            const badge = document.getElementById('mentionNotificationCount');
            if (badge) { badge.textContent = '0'; badge.classList.add('d-none'); }
        } catch (_) {
            window.location.reload();
        } finally {
            button.disabled = false;
        }
    });
})();
</script>
@endpush
