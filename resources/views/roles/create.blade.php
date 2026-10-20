@extends('layouts.app')
@extends('layouts.header')
@section('content')
<div class="container-fluid mt-80">
<div class="row">
    <div class="col-lg-12 mb-4 margin-tb">
        <div class="d-flex align-items-center justify-content-between shadow-sm p-3 rounded bg-white">
            <div class="pull-left">
                <h2>Create New Role</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-primary btn-sm" href="{{ route('roles.index') }}"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 p-4 mb-4 shadow-sm m-auto">
    <form method="POST" action="{{ route('roles.store') }}">
        @csrf
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 mb-4">
                <div class="form-group">
                    <strong>Name:</strong>
                    <input type="text" name="name" placeholder="Name" class="form-control mt-2">
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Permission:</strong>
                    <br/>
                    @foreach($permission as $value)
                        <label><input type="checkbox" name="permission[{{$value->id}}]" value="{{$value->id}}" class="name">
                        {{ $value->name }}</label>
                    <br/>
                    @endforeach
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                <button type="submit" class="btn btn-primary btn-sm mb-3"><i class="fa-solid fa-floppy-disk"></i> Submit</button>
            </div>
        </div>
    </form>
</div>
</div>
@endsection
