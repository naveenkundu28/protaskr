@extends('layouts.app')
@extends('layouts.header')

@section('content')
    <div class="container-fluid mt-80">
        <div style="padding: 0px 12px;">
            <div class="row">
                <div
                    class="col-lg-12 mb-4 margin-tb d-flex align-items-center justify-content-between bg-white shadow-sm p-3 rounded">
                    <div class="pull-left">
                        <h2>Create Lead Status</h2>
                    </div>
                    <div class="pull-right">
                        <a class="btn btn-primary btn-sm" href="{{ route('lead-statuses.index') }}"><i
                                class="fa fa-arrow-left"></i>
                            Back</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 p-4 mb-4 shadow-sm m-auto">
            <form method="POST" action="{{ route('lead-statuses.store') }}">
                @csrf
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Name: <span class="text-danger">*</span></strong>
                            <input type="text" name="name" placeholder="Lead Status Name" class="form-control mt-2"
                                required>
                        </div>
                    </div>
                    
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <button type="submit" class="btn btn-primary btn-sm mt-2 mb-3"><i
                                class="fa-solid fa-floppy-disk"></i>
                            Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
