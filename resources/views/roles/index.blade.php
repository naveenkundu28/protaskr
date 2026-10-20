@extends('layouts.app')
@extends('layouts.header')
@section('title')
    Roles
@endsection
@section('content')
<div class="container-fluid mt-80">
    <div style="padding: 0px 12px;">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h2 class="mb-0">Role Management</h2>
                    <form method="GET" action="{{ route('roles.index') }}" class="d-flex gap-2 auto-search-form" data-search-target="#tableSearchResults">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search roles">
                    </form>
                </div>
                <a href="{{ route('roles.create') }}" class="btn btn-primary">Create New Role</a>
            </div>
        </div>
    </div>


<div id="tableSearchResults">
    <table class="table table-bordered">
      <tr>
         <th width="100px">No</th>
         <th>Name</th>
         <th width="280px">Action</th>
      </tr>
        @foreach ($roles as $key => $role)
        <tr>
            <td>{{ ++$i }}</td>
            <td>{{ $role->name }}</td>
            <td>
                <a class="btn btn-info btn-sm" href="{{ route('roles.show',$role->id) }}"><i class="fa-solid fa-list"></i> Show</a>
                @can('role-edit')
                    <a class="btn btn-primary btn-sm" href="{{ route('roles.edit',$role->id) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                @endcan

                @can('role-delete')
                <form method="POST" action="{{ route('roles.destroy', $role->id) }}" style="display:inline">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Delete</button>
                </form>
                @endcan
            </td>
        </tr>
        @endforeach
    </table>

    {!! $roles->links('pagination::bootstrap-5') !!}
</div>
</div>
@endsection
