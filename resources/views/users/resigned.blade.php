@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/project-module.css') }}">
@endpush
@push('scripts')
<script>
    jQuery(function ($) {
        $(document).on('click', '.users-page .users-tabs a', async function (event) {
            event.preventDefault();
            const link = this;
            const current = document.querySelector('.users-page');
            if (!current) return;
            try {
                const response = await fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Unable to load users');
                const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                const next = doc.querySelector('.users-page');
                if (!next) throw new Error('User list is missing');
                current.replaceWith(next);
                history.pushState({}, '', link.href);
            } catch (error) { window.location.assign(link.href); }
        });
    });
</script>
@endpush
@section('content')
    <div class="container-fluid mt-80 users-page">
        <div class="pm-heading users-module-heading">
            <div class="inner_page_topbar">
                <h1>Users</h1>
                <div class="right_search_box">
                    <form method="GET" action="{{ route('users.resigned') }}" class="users-search auto-search-form" data-search-target="#tableSearchResults">
                        <div class="search_box">
                            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search resigned users..." aria-label="Search resigned users">
                            @if(request('search'))
                                <a href="{{ route('users.resigned') }}" class="search_clear_link" aria-label="Clear search"><i class="bi bi-x-lg"></i></a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="pm-tabs users-tabs">
            <a href="{{ route('users.index') }}">Team Members <span class="users-tab-count">({{ number_format($teamCount) }})</span></a>
            <a href="{{ route('users.index', ['group' => 'outsource']) }}">Outsource <span class="users-tab-count">({{ number_format($outsourceCount) }})</span></a>
            <a class="active" href="{{ route('users.resigned') }}">Resigned Users <span class="users-tab-count">({{ number_format($resignedCount) }})</span></a>
        </div>


        <section class="pm-card users-table-card">
            <div id="tableSearchResults">
                <div class="users-table-scroll">
                    <table class="table align-middle userListingTable has-resigned">
                        <thead>
                            <tr>
                                <th class="users-col-number">#</th>
                                <th class="users-col-employee">Employee ID</th>
                                <th class="users-col-joining">Joining date</th>
                                <th class="users-col-name">Name</th>
                                <th class="users-col-phone">Phone</th>
                                <th class="users-col-exp">Exp.</th>
                                <th class="users-col-skills">Primary skills</th>
                                <th class="users-col-tags">Tags</th>
                                <th class="users-col-reason">Resign reason</th>
                                <th class="users-col-last-date">Last working date</th>
                                <th class="users-col-action"><span class="visually-hidden">Actions</span><i class="bi bi-three-dots"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $user)
                                <tr>
                                    <td class="users-number">{{ ++$i }}</td>
                                    <td><span class="employee-id">{{ $user->employee_id ?: '—' }}</span></td>
                                    <td class="users-date">{{ $user->joining_date ? \Carbon\Carbon::parse($user->joining_date)->format('j M Y') : '—' }}</td>
                                    <td><div class="user-name-cell"><span class="user-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name ?: '?', 0, 1)) }}</span><span class="user-name-text">{{ $user->name }}</span></div></td>
                                    <td class="users-phone">{{ implode(', ', array_filter([$user->phone_no, $user->alt_phone_no])) ?: '—' }}</td>
                                    <td class="users-experience">{{ $user->experience !== null ? number_format((float) $user->experience, 1) . ' yrs' : '—' }}</td>
                                    <td><div class="user-chip-list">
                                        @forelse ($user->primarySkills as $skill)
                                            <span class="skill-item">{{ $skill->name }}</span>
                                        @empty <span class="user-empty-value">—</span> @endforelse
                                    </div></td>
                                    <td><div class="user-chip-list">
                                        @forelse ($user->tags as $tag)
                                            @php
                                                $tagColor = $tag->color ?: '#6c757d';
                                                $hex = ltrim($tagColor, '#');
                                                if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                                                $r = hexdec(substr($hex, 0, 2));
                                                $g = hexdec(substr($hex, 2, 2));
                                                $b = hexdec(substr($hex, 4, 2));
                                                $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                                                $textColor = $brightness > 140 ? '#111111' : '#ffffff';
                                            @endphp
                                            <span class="tag-item" style="background-color: {{ $tagColor }}; color: {{ $textColor }};">{{ $tag->name }}</span>
                                        @empty <span class="user-empty-value">—</span> @endforelse
                                    </div></td>
                                    <td class="users-reason">{{ $user->comment ?: '—' }}</td>
                                    <td class="users-date">{{ $user->last_working_date ? \Carbon\Carbon::parse($user->last_working_date)->format('j M Y') : '—' }}</td>
                                    <td class="text-center">
                                        <div class="project-actions user-project-actions">
                                            <button type="button" class="emp_status_action" aria-label="Actions for {{ $user->name }}"><i class="bi bi-three-dots"></i></button>
                                            <ul>
                                                @can('users-show')
                                                    <li><a href="{{ route('users.show', $user->id) }}"><i class="bi bi-person-lines-fill me-1"></i> View profile</a></li>
                                                @endcan
                                                @can('users-resign-restore')
                                                    <li><form action="{{ route('users.restore', $user->id) }}" method="POST">@csrf<button class="project-delete-action text-success" onclick="return confirm('Are you sure you want to restore this user?')"><i class="bi bi-arrow-counterclockwise me-1"></i> Restore user</button></form></li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="users-empty-row"><span class="users-empty-icon"><i class="bi bi-person-dash"></i></span><strong>No resigned users found</strong><span>Resigned users will appear here.</span></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="users-pagination">
                    <span>Showing {{ $data->firstItem() ?? 0 }}–{{ $data->lastItem() ?? 0 }} of {{ number_format($data->total()) }}</span>
                    {!! $data->links('pagination::bootstrap-5') !!}
                </div>
            </div>
        </section>
    </div>
@endsection
