{{-- Rows of the Time Log table; rendered with the page and again after a timer stops --}}
@forelse ($taskHistories as $taskHistory)
    <tr>
        <td data-id="{{ $taskHistory->id }}">{{ $loop->iteration }}</td>
        <td data-id="{{ $taskHistory->id }}">
            <span class="start-time-text">
                {{ $taskHistory->start_time }}
            </span>
            @if ($canEditTime)
                <div class="align-items-center d-flex gap-2 mt-1 time-edit d-none">
                    <div>
                        <input type="datetime-local" step="1"
                            class="form-control form-control-sm start-time-input"
                            value="{{ $taskHistory->start_time ? \Carbon\Carbon::parse($taskHistory->start_time)->format('Y-m-d\TH:i:s') : '' }}">
                    </div>
                </div>
            @endif
        </td>
        <td data-id="{{ $taskHistory->id }}">
            <span class="end-time-text">
                {{ $taskHistory->end_time }}
            </span>
            @if ($canEditTime)
                <div class="align-items-center d-flex gap-2 mt-1 time-edit d-none">
                    <div>
                        <input type="datetime-local" step="1"
                            class="form-control form-control-sm end-time-input"
                            value="{{ $taskHistory->end_time ? \Carbon\Carbon::parse($taskHistory->end_time)->format('Y-m-d\TH:i:s') : '' }}">
                    </div>
                </div>
            @endif
        </td>
        <td data-id="{{ $taskHistory->id }}">
            <span class="time-text">
                {{ $taskHistory->elapsed_time_formatted }}
            </span>
            @if ($canEditTime)
                <button class="btn btn-sm btn-primary edit-time ms-2">
                    <i class="bi bi-pencil"></i>
                </button>
                <div class="align-items-center d-flex gap-2 mt-1 time-edit d-none">
                    <div>
                        <input type="text" class="form-control form-control-sm time-input"
                            value="{{ $taskHistory->elapsed_time_formatted }}"
                            placeholder="HH:MM:SS">
                    </div>

                    <div class="mt-1">
                        <button class="btn btn-success btn-sm save-time">
                            <i class="bi bi-check"></i>
                        </button>
                        <button class="btn btn-secondary btn-sm cancel-time">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                <div class="time-edit-error text-danger small mt-1 d-none" role="alert"></div>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center text-muted py-4">No time has been tracked on this task yet.</td>
    </tr>
@endforelse
