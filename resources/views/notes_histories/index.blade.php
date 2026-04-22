@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div style="padding: 0px 12px;">
            <div class="row">
                <div class="d-flex justify-content-between align-items-center bg-white shadow-sm p-3 rounded mb-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <h2 class="mb-0">Notes Histories</h2>
                        <span class="text-muted">{{ $lead->lead }}</span>

                        <form method="GET" action="{{ route('leads.notes-histories.index', $lead->id) }}"
                            class="d-flex gap-2 auto-search-form">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="Search notes">
                        </form>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('leads.index') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('leads.notes-histories.create', $lead->id) }}" class="btn btn-primary">Create New
                            Notes</a>
                    </div>
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
                    <th>Status</th>
                    <th width="350px">Notes</th>
                    <th>Actions</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>

                @forelse ($notesHistories as $notesHistory)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>
                            {{ optional($notesHistory->leadStatus)->name
                                ? ucwords($notesHistory->leadStatus->name)
                                : '-' }}
                        </td>
                        <td class="jd-column">{{ $notesHistory->notes }}</td>
                        <td>{{ $notesHistory->actions }}</td>
                        <td>{{ $notesHistory->created_at ? $notesHistory->created_at->format('d-m-Y h:i A') : '' }}</td>
                        <td>
                            <div class="dropdown position-static">
                                <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-list-task"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item text-primary"
                                            href="{{ route('leads.notes-histories.edit', [$lead->id, $notesHistory->id]) }}">Edit</a>
                                    </li>

                                    <li>
                                        <form method="POST"
                                            action="{{ route('leads.notes-histories.destroy', [$lead->id, $notesHistory->id]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="dropdown-item text-danger"
                                                onclick="return confirm('Are You Sure You Want to Delete This notes history?')">Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No Notes History Found
                        </td>
                    </tr>
                @endforelse
            </table>
        </div>

        {!! $notesHistories->links('pagination::bootstrap-5') !!}
    </div>
@endsection
