@extends('layouts.app')

@section('content')
<div class="container">
    <div style="padding: 0px 8px;">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                <h2>Skills Management</h2>
                <a href="{{ route('skills.create') }}" class="btn btn-primary">Create New Skills</a>
            </div>
        </div>
    </div>

@session('success')
    <div class="alert alert-success" role="alert"> 
        {{ $value }}
    </div>
@endsession
<div class="table-responsive">
    <table class="table table-bordered table-sm text-nowrap">
        <tr>
            <th>No</th>
            <th>Name</th>
            {{-- <th>Roles</th> --}}
            <th width="280px">Action</th>
        </tr>
        @foreach ($skills as $key => $skill)
        <tr>
            <td>{{ ++$i }}</td>
            <td>{{ $skill->name }}</td>
            
            {{-- @if(!empty($user->getRoleNames()))
                @foreach($user->getRoleNames() as $v)
                <label class="badge bg-success">{{ $v }}</label>
                @endforeach
            @endif --}}
            </td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-list-task"></i>
                    </button>
                    <ul class="dropdown-menu py-0">

                        @can('user-show')
                            <li>
                                <a class="px-2 py-2 d-block text-decoration-none text-info" href="{{ route('skills.show',$skill->id) }}"><i
                                        class="fa-solid fa-list"></i> Show</a>
                            </li>
                        @endcan


                        @can('user-edit')
                            <li>
                                <hr class="my-1" />
                            </li>
                            <li>
                                <a class="px-2 py-2 d-block text-decoration-none text-primary" href="{{ route('skills.edit',$skill->id) }}"><i
                                        class="fa-solid fa-pen-to-square"></i> Edit</a>
                            </li>
                        @endcan



                        @can('user-delete')
                            <li>
                                <hr class="my-1" />
                            </li>

                            <form method="POST" action="{{ route('skills.destroy', $skill->id) }}" style="display:inline">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="px-2 py-2 border-0 px-0 text-danger text-start w-100 bg-white resignBtn" style="border-radius: 6px" onclick="return confirm('Are you sure you want to delete this skill?')"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                        @endcan

                    </ul>
                </div>
            </td>
            {{-- <td>
                <a class="btn btn-info btn-sm" href="{{ route('skills.show',$skill->id) }}"><i class="fa-solid fa-list"></i> Show</a>
                <a class="btn btn-primary btn-sm" href="{{ route('skills.edit',$skill->id) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                <form method="POST" action="{{ route('skills.destroy', $skill->id) }}" style="display:inline">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Delete</button>
                </form>
            </td> --}}
        </tr>
    @endforeach
    </table>
</div>
{!! $skills->links('pagination::bootstrap-5') !!}
</div>
@endsection
