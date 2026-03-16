@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div style="padding: 0px 12px;">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h2 class="mb-0">Tags Management</h2>
                    <form method="GET" action="{{ route('tags.index') }}" class="d-flex gap-2 auto-search-form" data-search-target="#tableSearchResults">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search tags">
                        {{-- <button class="btn btn-outline-secondary">Search</button> --}}
                    </form>
                </div>
                <a href="{{ route('tags.create') }}" class="btn btn-primary">Create New Tags</a>
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
    <table class="table table-bordered table-sm text-nowrap">
        <tr>
            <th>No</th>
            <th>Name</th>
            {{-- <th>Roles</th> --}}
            <th>Action</th>
        </tr>
        @foreach ($tags as $key => $tag)
        <tr>
            <td>{{ ++$i }}</td>
            <td>{{ $tag->name }}</td>
            
            {{-- @if(!empty($user->getRoleNames()))
                @foreach($user->getRoleNames() as $v)
                <label class="badge bg-success">{{ $v }}</label>
                @endforeach
            @endif --}}
            </td>
            <td>
                <div class="dropdown service-action-dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-list-task"></i>
                    </button>
                    <ul class="dropdown-menu py-0">
                        @can('user-show')
                            <li>
                                <a class="px-2 py-2 d-block text-decoration-none text-info" href="{{ route('tags.show',$tag->id) }}"><i
                                        class="fa-solid fa-list"></i> Show</a>
                            </li>
                        @endcan
                        @can('user-edit')
                            <li>
                                <hr class="my-1" />
                            </li>
                            <li>
                                <a class="px-2 py-2 d-block text-decoration-none text-primary" href="{{ route('tags.edit',$tag->id) }}"><i
                                        class="fa-solid fa-pen-to-square"></i> Edit</a>
                            </li>
                        @endcan
                        @can('user-delete')
                            <li>
                                <hr class="my-1" />
                            </li>

                            <form method="POST" action="{{ route('tags.destroy', $tag->id) }}" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-2 border-0 px-0 text-danger text-start w-100 bg-white resignBtn" style="border-radius: 6px" onclick="return confirm('Are you sure you want to delete this tags?')"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                        @endcan

                    </ul>
                </div>
                {{-- <a class="btn btn-info btn-sm" href="{{ route('tags.show',$tag->id) }}"><i class="fa-solid fa-list"></i> Show</a>
                <a class="btn btn-primary btn-sm" href="{{ route('tags.edit',$tag->id) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                <form method="POST" action="{{ route('tags.destroy', $tag->id) }}" style="display:inline">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this tags?')"><i class="fa-solid fa-trash"></i> Delete</button>
                </form> --}}
            </td>
        </tr>
    @endforeach
    </table>
</div>
{!! $tags->links('pagination::bootstrap-5') !!}
</div>
</div>
@endsection
