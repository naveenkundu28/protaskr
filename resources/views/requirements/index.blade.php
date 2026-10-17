@extends('layouts.app')
@section('content')
<div class="container-fluid mt-80">
    <div style="padding: 0px 12px;">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h2 class="mb-0">Requirements Management</h2>
                    <form method="GET" action="{{ route('requirements.index') }}" class="d-flex gap-2 auto-search-form" data-search-target="#tableSearchResults">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search requirements">
                        {{-- <button class="btn btn-outline-secondary">Search</button> --}}
                    </form>
                </div>
                <a href="{{ route('requirements.create') }}" class="btn btn-primary">Create New Requirement</a>
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
            <th>Requirement</th>
            <th>Action</th>
        </tr>
        @foreach ($requirements as $key => $requirement)
        <tr>
            <td>{{ ++$key }}</td>
            <td>{{ $requirement->requirement == 'ui_ux' ? 'UI-UX' : ($requirement->requirement ? ucwords(str_replace('_', ' ', $requirement->requirement)) : '') }}</td>
            <td>
                <div class="dropdown service-action-dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-list-task"></i>
                    </button>
                    <ul class="dropdown-menu py-0">
                        
                        @can('requirements-update')
                            <li>
                                <hr class="my-1" />
                            </li>
                            <li>
                                <a class="px-2 py-2 d-block text-decoration-none text-primary" href="{{ route('requirements.edit',$requirement->id) }}"><i
                                        class="fa-solid fa-pen-to-square"></i> Edit</a>
                            </li>
                        @endcan
                        @can('requirements-delete')
                            <li>
                                <hr class="my-1" />
                            </li>

                            <form method="POST" action="{{ route('requirements.destroy', $requirement->id) }}" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-2 border-0 px-0 text-danger text-start w-100 bg-white resignBtn" style="border-radius: 6px" onclick="return confirm('Are you sure you want to delete this requirement?')"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                        @endcan

                    </ul>
                </div>
            </td>
        </tr>
    @endforeach
    </table>
</div>
{!! $requirements->links('pagination::bootstrap-5') !!}
</div>
</div>
@endsection
