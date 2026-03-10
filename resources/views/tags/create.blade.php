@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-12 mb-4 margin-tb d-flex align-items-center justify-content-between">
                <div class="pull-left">
                    <h2>Create Tags</h2>
                </div>
                <div class="pull-right">
                    <a class="btn btn-primary btn-sm mb-2" href="{{ route('tags.index') }}"><i class="fa fa-arrow-left"></i>
                        Back</a>
                </div>
            </div>
        </div>

        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('tags.store') }}">
            @csrf
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                    <div class="form-group">
                        <strong>Tag Name:</strong>
                        <input type="text" name="name" placeholder="Tag Name" class="form-control">
                    </div>
                </div>

                <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                    <button type="submit" class="btn btn-primary btn-sm mt-2 mb-3"><i class="fa-solid fa-floppy-disk"></i>
                        Submit</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            $('.select2').select2({
                placeholder: "Select options",
                allowClear: true,
                width: '100%'
            });

        });
    </script>
@endpush

