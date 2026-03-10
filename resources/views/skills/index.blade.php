@extends('layouts.app')

@section('content')
<div class="container">
<div class="row">
    <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
        <h2>Master Skills Management</h2>
        <a href="{{ route('skills.create') }}" class="btn btn-primary">Create New Master Skills</a>
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
                <a class="btn btn-info btn-sm" href="{{ route('skills.show',$skill->id) }}"><i class="fa-solid fa-list"></i> Show</a>
                <a class="btn btn-primary btn-sm" href="{{ route('skills.edit',$skill->id) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                <form method="POST" action="{{ route('skills.destroy', $skill->id) }}" style="display:inline">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
    </table>
</div>
{!! $skills->links('pagination::bootstrap-5') !!}
</div>
@endsection
