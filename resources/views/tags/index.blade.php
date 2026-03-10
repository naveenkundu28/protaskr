@extends('layouts.app')

@section('content')
<div class="container">
<div class="row">
    <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
        <h2>Tags Management</h2>
        <a href="{{ route('tags.create') }}" class="btn btn-primary">Create New Tags</a>
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
                <a class="btn btn-info btn-sm" href="{{ route('tags.show',$tag->id) }}"><i class="fa-solid fa-list"></i> Show</a>
                <a class="btn btn-primary btn-sm" href="{{ route('tags.edit',$tag->id) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                <form method="POST" action="{{ route('tags.destroy', $tag->id) }}" style="display:inline">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
    </table>
</div>
{!! $tags->links('pagination::bootstrap-5') !!}
</div>
@endsection
