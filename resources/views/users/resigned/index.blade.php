@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div style="padding: 0px 12px">
            <div class="row">
                <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <h2 class="mb-0">Resigned Users</h2>
                        <form method="GET" action="{{ route('resign.users.index') }}" class="d-flex gap-2 auto-search-form" data-search-target="#tableSearchResults">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search resigned users">
                            {{-- <button class="btn btn-outline-secondary">Search</button> --}}
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <div id="tableSearchResults">
            <div class="table-responsive pb-4 service-table-responsive">
                <table class="table table-bordered table-sm text-nowrap">
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
                    <th>Resign Reason</th>
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
                                    <span>{{ $tag->name }}</span>
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
                        <td>
                            {{ $user->comment }}
                        </td>
                        <td>
                            <div class="dropdown service-action-dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-list-task"></i>
                                </button>
                                <ul class="dropdown-menu py-0">
                                    @can('user-show')
                                        <li class="px-2 py-2">
                                            <a class="w-100 text-decoration-none text-info"
                                                href="{{ route('users.show', $user->id) }}"><i class="fa-solid fa-list"></i>
                                                Show</a>
                                        </li>
                                    @endcan
                                    @can('user-edit')
                                        <li>
                                            <hr class="my-1">
                                        </li>
                                        <li class="px-2 py-2">
                                            <form action="{{ route('users.restore', $user->id) }}" method="POST">
                                                @csrf
                                                <button class="border-0 text-success text-start w-100 bg-white px-0"
                                                    onclick="return confirm('Are you sure you want to restore this user?')">
                                                    Restore
                                                </button>
                                            </form>
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

    </div>
@endsection
