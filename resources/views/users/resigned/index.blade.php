@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                <h2>Resigned Users</h2>
            </div>
        </div>


        <div class="table-responsive">
            <table class="table table-bordered table-sm text-nowrap">
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
                    <th>Resign Reason</th>
                    <th width="280px">Action</th>
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
                            {{ $user->comment }}
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu py-0">
                                    @can('user-show')
                                        <li class="px-2 py-2">
                                            <a class="w-100 text-decoration-none text-info"
                                                href="{{ route('users.show', $user->id) }}"><i class="fa-solid fa-list"></i>
                                                Show</a>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                            
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        {!! $data->links('pagination::bootstrap-5') !!}

    </div>
@endsection
