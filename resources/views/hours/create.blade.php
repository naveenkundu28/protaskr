@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12 mb-4 margin-tb">
                <div class="d-flex align-items-center justify-content-between shadow-sm p-3 rounded bg-white">
                    <div class="pull-left">
                        <h2>Master Hours</h2>
                    </div>
                    {{-- <div class="pull-right">
                        <a class="btn btn-primary btn-sm" href="{{ route('master.hours.create') }}"><i class="fa fa-arrow-left"></i>
                            Back</a>
                    </div> --}}
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('master.hours.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">Select Year</label>

                        <select name="year" id="year_select" class="form-control">

                            @foreach ($years as $year)
                                <option value="{{ $year }}"
                                    {{ old('year', $currentYear) == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach

                        </select>

                        @error('year')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    <div class="row">

                        @foreach ($months as $month)
                            <div class="col-md-2 mb-3">
                                <label class="form-label">{{ $month }}</label>

                                <input id="month_{{ $month }}" type="number" name="months[{{ $month }}]"
                                    class="form-control" value="{{ old('months.' . $month, $hours[$month]->hours ?? '') }}"
                                    placeholder="Enter hours">

                                @error('months.' . $month)
                                    <span class="text-danger">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        @endforeach

                    </div>

                    <div class="text-center">
                        <button class="btn btn-primary">
                            Save Hours
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $('#year_select').change(function() {

            let year = $(this).val()

            $.get("{{ route('master.hours.get', '') }}/" + year, function(data) {

                // clear all inputs first
                $('input[id^="month_"]').val('')

                // fill returned data
                $.each(data, function(month, item) {
                    $('#month_' + month).val(item.hours)
                })

            })
        })
    </script>
@endpush
