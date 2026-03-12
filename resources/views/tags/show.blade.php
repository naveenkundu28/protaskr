@extends('layouts.app')

@section('content')
<div class="container">
    <div style="padding: 0px 8px;">
        <div class="row">
            <div class="col-lg-12 margin-tb d-flex align-items-center justify-content-between mb-4 bg-white shadow-sm p-3 rounded">
                <div class="pull-left">
                    <h2> Show Tags</h2>
                </div>
                <div class="pull-right">
                    <a class="btn btn-primary" href="{{ route('tags.index') }}"> Back</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 p-4 mb-4 shadow-sm m-auto">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <strong>Tag Name: </strong> {{ $tag->name }}
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
</div>
@endsection
