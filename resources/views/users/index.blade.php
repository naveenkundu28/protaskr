@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/project-module.css') }}">
@endpush
@section('content')
    <div class="container-fluid mt-80 users-page">
        <div class="pm-heading users-module-heading">
            <div class="inner_page_topbar">
                <h1>Users</h1>
                <div class="right_search_box">
                    <form method="GET" action="{{ route('users.index') }}" class="users-search auto-search-form" data-search-target="#tableSearchResults">
                        <input type="hidden" name="group" value="{{ $group }}">
                        @if(request('tag_id')) <input type="hidden" name="tag_id" value="{{ request('tag_id') }}"> @endif
                        <div class="search_box">
                            <button type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
                            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search users..." aria-label="Search users">
                            @if(request('search'))
                                <a href="{{ route('users.index', array_filter(['group' => $group, 'tag_id' => request('tag_id')])) }}" class="search_clear_link" aria-label="Clear search"><i class="bi bi-x-lg"></i></a>
                            @endif
                        </div>
                    </form>
                    @can('users-create')
                        <a class="btn btn-primary pm-button" href="{{ route('users.create') }}"><i class="bi bi-plus-lg"></i> Create User</a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="pm-tabs users-tabs">
            <a class="{{ $group === 'team' ? 'active' : '' }}" href="{{ route('users.index', array_filter(['group' => null, 'search' => request('search'), 'tag_id' => request('tag_id')])) }}">Team Members <span class="users-tab-count">({{ number_format($teamCount) }})</span></a>
            <a class="{{ $group === 'outsource' ? 'active' : '' }}" href="{{ route('users.index', array_filter(['group' => 'outsource', 'search' => request('search'), 'tag_id' => request('tag_id')])) }}">Outsource <span class="users-tab-count">({{ number_format($outsourceCount) }})</span></a>
            <a href="{{ route('users.resigned') }}">Resigned Users <span class="users-tab-count">({{ number_format($resignedCount) }})</span></a>
        </div>

        @session('success')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ $value }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endsession

        <section class="pm-card users-table-card">
            <div id="tableSearchResults">
                <div class="users-table-scroll">
                    <table class="table align-middle userListingTable {{ request('work_type') ? 'has-total-hours' : '' }}">
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
                                @if(request('work_type')) <th class="users-col-hours">Total hours</th> @endif
                                <th class="users-col-action"><span class="visually-hidden">Actions</span><i class="bi bi-three-dots"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $user)
                                <tr>
                                    <td class="users-number">{{ ++$i }}</td>
                                    <td><span class="employee-id">{{ $user->employee_id ?: '—' }}</span></td>
                                    <td class="users-date">{{ $user->joining_date ? \Carbon\Carbon::parse($user->joining_date)->format('j M Y') : '—' }}</td>
                                    <td>
                                        <div class="user-name-cell">
                                            <span class="user-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name ?: '?', 0, 1)) }}</span>
                                            <span class="user-name-text">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="users-phone">{{ implode(', ', array_filter([$user->phone_no, $user->alt_phone_no])) ?: '—' }}</td>
                                    <td class="users-experience">{{ $user->experience !== null ? number_format((float) $user->experience, 1) . ' yrs' : '—' }}</td>
                                    <td>
                                        <div class="user-chip-list">
                                            @forelse ($user->primarySkills as $skill)
                                                <span class="skill-item">{{ $skill->name }}</span>
                                            @empty <span class="user-empty-value">—</span> @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-chip-list">
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
                                        </div>
                                    </td>
                                    @if(request('work_type'))
                                        <td class="users-experience">{{ isset($hoursByUser[$user->id]) ? number_format($hoursByUser[$user->id], 2) : '0.00' }}</td>
                                    @endif
                                    <td class="text-end">
                                        <div class="project-actions user-project-actions">
                                            <button type="button" class="emp_status_action" aria-label="Actions for {{ $user->name }}">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul>
                                                @can('users-show')
                                                    <li><a href="{{ route('users.show', $user->id) }}"><i class="bi bi-person-lines-fill me-1"></i> View profile</a></li>
                                                @endcan
                                                @can('users-update')
                                                    <li><a href="{{ route('users.edit', $user->id) }}"><i class="bi bi-pencil-square me-1"></i> Edit user</a></li>
                                                @endcan
                                                @can('users-permissions')
                                                    <li><a href="{{ route('users.permissions.edit', $user->id) }}"><i class="bi bi-shield-lock me-1"></i> Permissions</a></li>
                                                @endcan
                                                @can('users-resign')
                                                    <li><button class="project-delete-action text-danger resignBtn" type="button" data-id="{{ $user->id }}" data-user-type="{{ strtolower($user->user_type) }}" data-bs-toggle="modal" data-bs-target="#resignModal"><i class="bi bi-person-dash me-1"></i> Resign user</button></li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ request('work_type') ? 10 : 9 }}" class="users-empty-row">
                                        <span class="users-empty-icon"><i class="bi bi-person-search"></i></span>
                                        <strong>No users found</strong>
                                        <span>Try a different name, email, skill, or role.</span>
                                    </td>
                                </tr>
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

        <div class="modal fade" id="resignModal" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" id="resignForm">
                    @csrf
                    @method('DELETE')

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="resignModalTitle">Resign Employee</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <label class="form-label">Comment <span class="text-danger">*</span></label>
                            <textarea name="comment" class="form-control" required></textarea>
                            <div class="mt-3">
                                <label class="form-label">Last Working Date <span class="text-danger">*</span></label>
                                <input type="date" name="last_working_date" class="form-control" value="{{ old('last_working_date') }}" required>
                            </div>

                            <input type="hidden" name="user_id" id="resignUserId" value="{{ old('user_id') }}">

                            @error('comment')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            @error('last_working_date')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger" id="resignModalButton">Confirm Resign</button>
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

            $('.resignBtn').click(function() {

                let userId = $(this).data('id');
                let userType = ($(this).data('user-type') || '').toString().toLowerCase();
                let modalTitle = userType === 'external' ? 'Unassigned Employee' : 'Resign Employee';
                let modalButton = userType === 'external' ? 'Confirm Unassigned' : 'Confirm Resign';

                $('#resignUserId').val(userId);
                $('#resignModalTitle').text(modalTitle);
                $('#resignModalButton').text(modalButton);
                
                let url = "{{ route('users.destroy', ':id') }}";
                url = url.replace(':id', userId);

                $('#resignForm').attr('action', url);

            });

            @if ($errors->has('comment') || $errors->has('last_working_date'))
                let userId = "{{ old('user_id') }}";

                let url = "{{ route('users.destroy', ':id') }}";
                url = url.replace(':id', userId);

                $('#resignForm').attr('action', url);

                var resignModal = new bootstrap.Modal($('#resignModal')[0]);
                resignModal.show();
            @endif

        });
    </script>
@endpush
