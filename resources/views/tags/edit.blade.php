@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div style="padding: 0px 12px;">
        <div class="row">
            <div class="col-lg-12 margin-tb d-flex align-items-center justify-content-between mb-4 bg-white shadow-sm p-3 rounded">
                <div class="pull-left">
                    <h2>Edit Tags</h2>
                </div>
                <div class="pull-right">
                    <a class="btn btn-primary btn-sm" href="{{ route('tags.index') }}"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
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

    <div class="card border-0 p-4 mb-4 shadow-sm m-auto">
        <form method="POST" action="{{ route('tags.update', $tag->id) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                    <div class="form-group">
                        <strong>Tag Name:</strong>
                        <input type="text" name="name" placeholder="Tag Name" class="form-control mt-2" value="{{ $tag->name }}">
                    </div>
                </div>
                {{-- <div class="col-xs-12 col-sm-12 col-md-12 mb-4">
                    <div class="form-group">
                        <strong>Role:</strong>
                        <select name="roles[]" class="form-control select2" multiple="multiple">
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" {{ isset($userRole[$value]) ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div> --}}
                <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                    <button type="submit" class="btn btn-primary btn-sm mt-2 mb-3"><i class="fa-solid fa-floppy-disk"></i> Submit</button>
                </div>
            </div>
        </form>
    </div>
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