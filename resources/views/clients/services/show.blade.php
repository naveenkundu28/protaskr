@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="card border-0 p-4 mb-4 shadow-sm m-auto">
            <div class="row">
                <div class="col-lg-12 d-flex justify-content-between align-items-center">
                    <h2>Show Service</h2>
                    <div>
                        <a class="btn btn-primary" href="{{ route('clients.services.index', $client->id) }}">
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 p-4 mb-4 shadow-sm m-auto">
            <div class="row">
    
                <div class="col-md-6 mb-3">
                    <strong>Service Type:</strong><br>
                    {{ ucfirst($service->service_type) }}
                </div>
    
                <div class="col-md-6 mb-3">
                    <strong>Service ID:</strong><br>
                    {{ $service->service_id }}
                </div>
    
                <div class="col-md-6 mb-3">
                    <strong>Front Resource:</strong><br>
                    {{ $service->frontResource->name ?? '-' }}
                </div>
    
                <div class="col-md-6 mb-3">
                    <strong>Working Resource:</strong><br>
                    {{ $service->workingResource->name ?? '-' }}
                </div>
    
                <div class="col-md-6 mb-3">
                    <strong>Hours:</strong><br>
                    {{ $service->hours }}
                </div>
    
                <div class="col-md-6 mb-3">
                    <strong>Month:</strong><br>
                    <td>{{ $service->created_at ? $service->created_at->format('F') : '' }}</td>
                </div>
    
                <div class="col-md-6 mb-3">
                    <strong>Status:</strong><br>
                    {{ ucfirst($service->service_status) }}
                </div>
    
                <div class="col-md-6 mb-3">
                    <strong>Start Date:</strong><br>
                    {{ $service->start_date ? \Carbon\Carbon::parse($service->start_date)->format('j F Y') : '' }}
                </div>
    
                <div class="col-md-6 mb-3">
                    <strong>End Date:</strong><br>
                    {{ $service->end_date ? \Carbon\Carbon::parse($service->end_date)->format('j F Y') : '' }}
                </div>
    
                @if ($service->cancel_reason)
                    <div class="col-md-6 mb-3">
                        <strong>Cancel Reason:</strong><br>
                        {{ $service->cancel_reason }}
                    </div>
                @endif
    
            </div>
        </div>

    </div>
@endsection
