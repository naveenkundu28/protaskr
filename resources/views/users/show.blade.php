@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12 margin-tb mb-4">
            <div class="d-flex align-items-center justify-content-between shadow-sm p-3 rounded bg-white">
                <div class="pull-left">
                    <h2> {{ $user->deleted_at == null ? 'Show User' : 'Show Resign User' }}</h2>
                </div>
                <div class="pull-right">
                    <a class="btn btn-primary" href="{{ route($user->deleted_at == null ? 'users.index' : 'resign.users.index') }}"> Back</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 p-4 mb-4 shadow-sm m-auto">
        
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <strong>User Type:</strong><br>
                    {{ $user->user_type }}
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <strong>Employee ID:</strong><br>
                    {{ $user->employee_id }}
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <strong>Joining Date:</strong><br>
                    {{ $user->joining_date ? \Carbon\Carbon::parse($user->joining_date)->format('j F Y') : '' }}
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <strong>Name:</strong><br>
                    {{ $user->name }}
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <strong>Email:</strong><br>
                    {{ $user->email }}
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <strong>Experience:</strong><br>
                    {{ $user->experience }} Years
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <strong>Phone No:</strong><br>
                    {{ implode(' , ', array_filter([$user->phone_no, $user->alt_phone_no])) }}
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <strong>Primary Skills:</strong><br>
                    @foreach ($user->primarySkills as $skill)
                        <span class="skill-item">{{ $skill->name }}</span>
                    @endforeach
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <strong>Secondary Skills:</strong><br>
                    @foreach ($user->secondarySkills as $skill)
                        <span class="skill-item skill-bg-secondary">{{ $skill->name }}</span>
                    @endforeach
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <strong>Tags list:</strong><br>
                    @foreach ($user->tags as $tag)
                        <span>{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                <div class="form-group">
                    <strong>Roles:</strong>
                    @if (!empty($user->getRoleNames()))
                        @foreach ($user->getRoleNames() as $v)
                            @if ($v == 'Admin')
                                <label class="skill-item admin-bg">{{ $v }}</label>
                            @else
                                <label class="">{{ $v }}</label>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>


            @if ($user->deleted_at && $user->comment)
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <strong>Comment:</strong><br>
                        {{ $user->comment }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
