@extends('layouts.app')

@section('title')
    Services - {{ $client->name }}
@endsection

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center bg-white mb-4 shadow-sm p-3 rounded">
            <h2>Services - {{ $client->name }}</h2>

            @can('service-create')
                <a href="{{ route('clients.services.create', $client->id) }}" class="btn btn-primary">
                    Add Service
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <table class="table table-bordered">
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Resources</th>
                <th>Service ID</th>
                <th>Hours</th>
                <th>Status</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th width="180">Action</th>
            </tr>

            @foreach ($services as $service)
                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>{{ ucfirst($service->service_type) }}</td>
                    
                    <td>
                        @foreach ($service->users as $user)
                            <span class="badge bg-secondary">{{ $user->name }}</span>
                        @endforeach
                    </td>
                    
                    <td>{{ $service->service_id }}</td>

                    <td>{{ $service->hours }}</td>

                    <td>{{ ucfirst($service->service_status) }}</td>


                    <td>{{ \Carbon\Carbon::parse($service->start_date)->format('j F Y') }}</td>

                    <td>{{ \Carbon\Carbon::parse($service->start_date)->format('j F Y') }}</td>

                    <td>

                        <div class="dropdown">

                            <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                Actions
                            </button>

                            <ul class="dropdown-menu">

                                @can('service-edit')
                                    <li class="px-2">
                                        <a href="{{ route('clients.services.edit', [$client->id, $service->id]) }}"
                                            class="btn btn-warning w-100">
                                            Edit
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                @endcan

                                @can('service-delete')
                                    <li class="px-2">
                                        <form action="{{ route('clients.services.destroy', [$client->id, $service->id]) }}"
                                            method="POST">

                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger w-100"
                                                onclick="return confirm('Delete this service?')">
                                                Delete
                                            </button>

                                        </form>
                                    </li>
                                @endcan

                            </ul>

                        </div>

                    </td>

                </tr>
            @endforeach

        </table>

        {!! $services->links('pagination::bootstrap-5') !!}

    </div>
@endsection
