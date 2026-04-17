@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div style="padding: 0px 12px;">
            <div class="row">
                <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <h2 class="mb-0">Leads Management</h2>

                        <form method="GET" action="{{ route('leads.index') }}" class="d-flex gap-2 auto-search-form">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="Search leads">
                        </form>
                    </div>

                    <a href="{{ route('leads.create') }}" class="btn btn-primary">Create New Lead</a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-sm text-nowrap">
                <tr>
                    <th>No</th>
                    <th>Lead</th>
                    <th>Status</th>
                    <th>TL</th>
                    <th>Team</th>
                    <th>Requirement</th>
                    <th>Claimed Group</th>
                    <th>Call Done</th>
                    <th>CAM Member</th>
                    <th>Date</th>
                    <th>JD</th>
                    <th>Notes</th>
                    <th>Action</th>
                </tr>

                @foreach ($leads as $key => $lead)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $lead->lead }}</td>
                        {{-- Latest Status --}}
                        <td>
                            {{ optional($lead->notesHistories->last())->status }}
                        </td>
                        <td>{{ $lead->tlUser->name ?? '' }}</td>
                        <td>{{ $lead->teamUser->name ?? '' }}</td>
                        <td>{{ ucwords(str_replace('_', '-', $lead->requirement->requirement ?? '')) }}</td>


                        <td>{{ $lead->claimed_group }}</td>

                        <td>{{ $lead->call_done ? 'Yes' : 'No' }}</td>
                        <td>{{ $lead->cam_member }}</td>
                        <td>{{ \Carbon\Carbon::parse($lead->date)->format('d-m-Y') }}</td>
                        <td>{{ $lead->jd }}</td>
                        {{-- <td>{{ $lead->notesHistories->notes ?? '' }}</td> --}}
                        <td>
                            {{ optional($lead->notesHistories()->latest()->first())->notes }}
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    Action
                                </button>
                                <ul class="dropdown-menu">

                                    <li>
                                        <a class="dropdown-item text-primary"
                                            href="{{ route('leads.edit', $lead->id) }}">Edit</a>
                                    </li>

                                    <li>
                                        <form method="POST" action="{{ route('leads.destroy', $lead->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="dropdown-item text-danger"
                                                onclick="return confirm('Are You Sure You Want to Delete This lead?')">Delete</button>
                                        </form>
                                    </li>

                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>

        {!! $leads->links('pagination::bootstrap-5') !!}

    </div>
@endsection
