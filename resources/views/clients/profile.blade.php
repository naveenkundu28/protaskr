@extends('layouts.app')
@section('title')
    {{ $client->name }} - Client Profile
@endsection
@section('content')
    <div class="container-fluid mt-80">
        <h2 class="mb-4 shadow-sm p-3 rounded bg-white text-center"> {{ $client->name }} - Profile</h2>

        @if (session('generated_password'))
            <div class="alert alert-warning">
                <strong>Password:</strong> <code>{{ session('generated_password') }}</code>
                <div>Copy it now. It is stored encrypted and cannot be shown again.</div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Login Details</h5>
                        <p class="card-text"><strong>Name:</strong> {{ $profile->name }}</p>
                        <p class="card-text"><strong>Email:</strong> {{ $profile->email }}</p>
                        <p class="card-text"><strong>Role:</strong> {{ $profile->role?->name }}</p>
                        <p class="card-text"><strong>Created:</strong> {{ $profile->created_at?->format('j F Y') }}</p>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Client Info</h5>
                        <p class="card-text"><strong>Client:</strong> {{ $client->name }}</p>
                        @if ($client->client_email)
                            <p class="card-text"><strong>Client Email:</strong> {{ $client->client_email }}</p>
                        @endif
                        @if ($client->client_code)
                            <p class="card-text"><strong>Client Code:</strong> {{ $client->client_code }}</p>
                        @endif
                        @if ($client->project_name)
                            <p class="card-text"><strong>Project Name:</strong> {{ $client->project_name }}</p>
                        @endif
                        <p class="card-text"><strong>Status:</strong> {{ ucwords(str_replace('_', ' ', $client->status)) }}</p>
                        <a href="{{ route('clients.show', $client->id) }}" class="btn btn-primary">View Client</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                @can('clients-profile-update')
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Update Password</h5>
                            <form action="{{ route('clients.profile.password', $client->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" id="password" class="form-control" required
                                        minlength="8" autocomplete="new-password">
                                    @error('password')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control" required minlength="8" autocomplete="new-password">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </form>
                        </div>
                    </div>
                @endcan

                @can('clients-profile-delete')
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Delete Profile</h5>
                            <p class="card-text">This removes the login only. The client and its data stay as they are.</p>
                            <form action="{{ route('clients.profile.destroy', $client->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this client profile?')">
                                    Delete Profile
                                </button>
                            </form>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>
@endsection
