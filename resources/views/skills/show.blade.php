@extends('layouts.app')

@section('content')
<div class="container">
<div class="row">
    <div class="col-lg-12 margin-tb d-flex align-items-center justify-content-between mb-4">
        <div class="pull-left">
            <h2> Show Master Skills</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('skills.index') }}"> Back</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="form-group">
            <strong>Skill Name:</strong><br>
            {{ $skills->name }}
        </div>
    </div>
    
    {{-- <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Roles:</strong>
            @if(!empty($user->getRoleNames()))
                @foreach($user->getRoleNames() as $v)
                    <span class="badge bg-danger">{{ $v }}</span>
                @endforeach
            @endif
        </div>
    </div> --}}
</div>
</div>
@endsection
