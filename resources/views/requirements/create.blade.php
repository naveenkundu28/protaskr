@extends('layouts.app')
@section('content')
    <div class="container-fluid mt-80">
        <div style="padding: 0px 12px;">
            <div class="row">
                <div
                    class="col-lg-12 mb-4 margin-tb d-flex align-items-center justify-content-between bg-white shadow-sm p-3 rounded">
                    <div class="pull-left">
                        <h2>Create Requirement</h2>
                    </div>
                    <div class="pull-right">
                        <a class="btn btn-primary btn-sm" href="{{ route('requirements.index') }}"><i
                                class="fa fa-arrow-left"></i>
                            Back</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 p-4 mb-4 shadow-sm m-auto">
            <form method="POST" action="{{ route('requirements.store') }}">
                @csrf
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                        <div class="form-group">
                            <strong>Requirement: <span class="text-danger">*</span></strong>
                            <input type="text" name="requirement" placeholder="Requirement" class="form-control mt-2"
                                required>

                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <button type="submit" class="btn btn-primary btn-sm mt-2 mb-3"><i
                                    class="fa-solid fa-floppy-disk"></i>
                                Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
