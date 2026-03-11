@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="card">
            <div class="card-header">
                <h4>Master Hours</h4>
            </div>

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

                        <select name="year" class="form-control">

                            @foreach ($years as $year)
                                <option value="{{ $year }}" {{ old('year', $currentYear) == $year ? 'selected' : '' }}>
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
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ $month }}</label>

                                <input type="number" name="months[{{ $month }}]" class="form-control" value="{{ old('months.'.$month) }}" placeholder="Enter hours">

                                @error('months.' . $month)
                                    <span class="text-danger">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        @endforeach

                    </div>

                    <button class="btn btn-primary">
                        Save Hours
                    </button>

                </form>

            </div>
        </div>

    </div>
@endsection
