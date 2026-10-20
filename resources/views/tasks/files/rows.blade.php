{{-- Rows of the Files tab on the task page. Also returned after an upload to refresh the list in place. --}}
@php($canUpdateFiles = auth()->user()->can('tasks-update', $task) && $task->story?->status !== 'completed')
@forelse ($files as $file)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $file->name ?: basename($file->path) }}</td>
        <td class="text-capitalize">{{ $file->type }}</td>
        <td>{{ $file->created_at?->format('d M Y') }}</td>
        <td>
            <div class="task-file-actions">
                <a href="{{ route('tasks.files.show', [$task->id, $file->id]) }}" target="_blank"
                    class="task-tool-btn is-sm is-view" title="View"><i class="bi bi-eye"></i></a>
                <a href="{{ route('tasks.files.download', [$task->id, $file->id]) }}"
                    class="task-tool-btn is-sm is-download" title="Download"><i class="bi bi-download"></i></a>
                @if ($canUpdateFiles)
                    <a href="{{ route('tasks.files.edit', [$task->id, $file->id]) }}" class="task-tool-btn is-sm is-back"
                        title="Edit"><i class="bi bi-pencil-square"></i></a>
                    <form action="{{ route('tasks.files.destroy', [$task->id, $file->id]) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this file?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="task-tool-btn is-sm is-delete" title="Delete"><i
                                class="bi bi-trash"></i></button>
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center text-muted py-4">No files have been uploaded for this task.</td>
    </tr>
@endforelse
