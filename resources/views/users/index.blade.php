@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div style="padding: 0px 12px">
            <div class="row">
                <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                    <h2>Users Management</h2>
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
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-nowrap userListingTable">
                <tr>
                    <th>No</th>
                    <th>User Type</th>
                    <th>Employee ID</th>
                    <th>Joining Date</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone No</th>
                    <th>Alternate Phone No</th>
                    <th>Experience</th>
                    <th>Primary Skills</th>
                    <th>Secondary Skills</th>
                    <th>Tags</th>
                    <th>Roles</th>
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
                        <td>{{ $user->phone_no }}</td>
                        <td>{{ $user->alt_phone_no }}</td>
                        <td>{{ $user->experience }}</td>
                        <td>
                            @if (!empty($user->primarySkills))
                                @foreach ($user->primarySkills as $skill)
                                    <span class="badge bg-primary">{{ $skill->name }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            @if (!empty($user->secondarySkills))
                                @foreach ($user->secondarySkills as $skill)
                                    <span class="badge bg-secondary">{{ $skill->name }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            @if (!empty($user->tags))
                                @foreach ($user->tags as $tag)
                                    <span class="badge bg-info">{{ $tag->name }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            @if (!empty($user->getRoleNames()))
                                @foreach ($user->getRoleNames() as $v)
                                    <label class="badge bg-success">{{ $v }}</label>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
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
                                                data-id="{{ $user->id }}" data-bs-toggle="modal"
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

        <div class="modal fade" id="resignModal" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" id="resignForm">
                    @csrf
                    @method('DELETE')

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Resign Employee</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <label class="form-label">Comment <span class="text-danger">*</span></label>
                            <textarea name="comment" class="form-control" required></textarea>

                            <input type="hidden" name="user_id" id="resignUserId" value="{{ old('user_id') }}">

                            @error('comment')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">Confirm Resign</button>
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

                $('#resignUserId').val(userId);
                let url = "{{ route('users.destroy', ':id') }}";
                url = url.replace(':id', userId);

                $('#resignForm').attr('action', url);

            });

            @if ($errors->has('comment'))
                let userId = "{{ old('user_id') }}";

                let url = "{{ route('users.destroy', ':id') }}";
                url = url.replace(':id', userId);

                $('#resignForm').attr('action', url);

                var resignModal = new bootstrap.Modal($('#resignModal')[0]);
                resignModal.show();
            @endif

        })
    </script>
@endpush
