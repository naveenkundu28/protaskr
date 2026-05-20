@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div style="padding: 0px 12px">
            <div class="row">
                <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <h2 class="mb-0">Users Management</h2>
                        <form method="GET" action="{{ route('users.index') }}" class="d-flex gap-2 auto-search-form" data-search-target="#tableSearchResults">
                            @if(request('tag_id'))
                                <input type="hidden" name="tag_id" value="{{ request('tag_id') }}">
                            @endif
                            @if(request('tag_scope'))
                                <input type="hidden" name="tag_scope" value="{{ request('tag_scope') }}">
                            @endif
                            @if(request('work_type'))
                                <input type="hidden" name="work_type" value="{{ request('work_type') }}">
                            @endif
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search users">
                            {{-- <button class="btn btn-outline-secondary">Search</button> --}}
                        </form>
                    </div>
                    <div>
                        <a href="{{ route('resign.users.index') }}" class="btn btn-warning">Resigned Users</a>
                        <a href="{{ route('users.create') }}" class="btn btn-primary">Create New User</a>
                    </div>
                </div>
            </div>
        </div>

        @session('success')
            <div class="alert alert-success" role="alert">
                {{ $value }}
            </div>
        @endsession
        <div id="tableSearchResults">
            <div class="table-responsive service-table-responsive">
                <table class="table table-bordered table-sm text-nowrap userListingTable">
                <tr>
                    <th>No</th>
                    <th>User Type</th>
                    <th>Emp ID</th>
                    <th>Joining Date</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Nos</th>
                    <th>Exp</th>
                    <th>Primary Skills</th>
                    <th>Secondary Skills</th>
                    <th>Tags</th>
                    <th>Roles</th>
                    @if(request('work_type'))
                        <th>Total Hours</th>
                    @endif
                    <th>Action</th>
                </tr>
                @foreach ($data as $key => $user)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $user->user_type }}</td>
                        <td>{{ $user->employee_id }}</td>
                        <td>{{ $user->joining_date ? \Carbon\Carbon::parse($user->joining_date)->format('j F Y') : '' }}
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ implode(' , ', array_filter([$user->phone_no, $user->alt_phone_no])) }}</td>
                        <td>{{ $user->experience }}</td>
                        <td class="text-wrap">
                            @if (!empty($user->primarySkills))
                                @foreach ($user->primarySkills as $skill)
                                    <span class="skill-item">{{ $skill->name }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td class="text-wrap">
                            @if (!empty($user->secondarySkills))
                                @foreach ($user->secondarySkills as $skill)
                                    <span class="skill-item skill-bg-secondary">{{ $skill->name }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            @if (!empty($user->tags))
                                @foreach ($user->tags as $tag)
                                    @php
                                        $tagColor = $tag->color ?: '#6c757d';
                                        $hex = ltrim($tagColor, '#');
                                        if (strlen($hex) === 3) {
                                            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                                        }
                                        $r = hexdec(substr($hex, 0, 2));
                                        $g = hexdec(substr($hex, 2, 2));
                                        $b = hexdec(substr($hex, 4, 2));
                                        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                                        $textColor = $brightness > 140 ? '#111111' : '#ffffff';
                                    @endphp
                                    <span class="tag-item" style="background-color: {{ $tagColor }}; color: {{ $textColor }};">{{ $tag->name }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            @if (!empty($user->getRoleNames()))
                                @foreach ($user->getRoleNames() as $v)
                                    @if ($v == 'Admin')
                                        <label class="skill-item admin-bg">{{ $v }}</label>
                                    @else
                                        <label class="">{{ $v }}</label>
                                    @endif
                                @endforeach
                            @endif
                        </td>
                        @if(request('work_type'))
                            <td>{{ isset($hoursByUser[$user->id]) ? number_format($hoursByUser[$user->id], 2) : '0.00' }}</td>
                        @endif
                        <td>
                            <div class="dropdown service-action-dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-list-task"></i>
                                </button>
                                <ul class="dropdown-menu py-0">

                                    @can('user-show')
                                        <li>
                                            <a class="px-2 py-2 d-block text-decoration-none text-info" href="{{ route('users.show', $user->id) }}"><i
                                                    class="fa-solid fa-list"></i> Show</a>
                                        </li>
                                    @endcan


                                    @can('user-edit')
                                        <li>
                                            <hr class="my-1" />
                                        </li>
                                        <li>
                                            <a class="px-2 py-2 d-block text-decoration-none text-primary" href="{{ route('users.edit', $user->id) }}"><i
                                                    class="fa-solid fa-pen-to-square"></i> Edit</a>
                                        </li>
                                    @endcan



                                    @can('user-delete')
                                        <li>
                                            <hr class="my-1" />
                                        </li>

                                        <li >
                                            <button class="px-2 py-2 border-0 px-0 text-danger text-start w-100 bg-white resignBtn"
                                                data-id="{{ $user->id }}" data-user-type="{{ strtolower($user->user_type) }}" data-bs-toggle="modal"
                                                data-bs-target="#resignModal">
                                                Resign
                                            </button>
                                        </li>
                                    @endcan

                                </ul>
                            </div>
                            {{-- <a class="btn btn-info btn-sm" href="{{ route('users.show', $user->id) }}"><i
                                    class="fa-solid fa-list"></i> Show</a>
                            <a class="btn btn-primary btn-sm" href="{{ route('users.edit', $user->id) }}"><i
                                    class="fa-solid fa-pen-to-square"></i> Edit</a>
                            <button class="btn btn-danger btn-sm resignBtn" data-id="{{ $user->id }}"
                                data-bs-toggle="modal" data-bs-target="#resignModal">
                                Resign
                            </button> --}}
                            {{-- <form method="POST" action="{{ route('users.destroy', $user->id) }}" style="display:inline">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to resign this employee?')"><i
                                        class="fa-solid fa-trash"></i> Resign</button>
                            </form> --}}
                        </td>
                    </tr>
                @endforeach
                </table>
            </div>
            {!! $data->links('pagination::bootstrap-5') !!}
        </div>

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
