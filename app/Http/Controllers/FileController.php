<?php
namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Task;

class FileController extends Controller
{
    public function index(Task $task)
    {
        $this->authorize('tasks-show', $task);
        $task->loadMissing('story');

        $files = Auth::user()->files()->where('task_id', $task->id)->latest()->get();
        return view('tasks.files.index', compact('files', 'task'));
    }

    public function create(Task $task)
    {
        $this->authorize('tasks-update', $task);
        $this->ensureTaskStoryIsEditable($task);

        return view('tasks.files.create', compact('task'));
    }

    public function show(Task $task, File $file)
    {
        $this->authorize('tasks-show', $task);

        $this->ensureTaskFileRelation($task, $file);

        abort_unless(Storage::disk('public')->exists($file->path), 404);

        return Storage::disk('public')->response($file->path, $this->buildDownloadName($file));
    }

    public function store(Request $request, Task $task)
    {
        $this->authorize('tasks-update', $task);
        $this->ensureTaskStoryIsEditable($task);

        $allowed = 'jpeg,png,jpg,gif,svg,doc,docx,pdf,txt,html,css,js,php,java,c,cpp';

        // `files[]` comes from the task page (one or many); `file` from the single-file page
        $validator = Validator::make($request->all(), [
            'files' => 'required_without:file|array|max:20',
            'files.*' => 'file|mimes:' . $allowed,
            'file' => 'required_without:files|file|mimes:' . $allowed,
            'names' => 'nullable|array',
            'names.*' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:project,docs,txt,code,image',
        ], [
            'files.required_without' => 'Choose at least one file to upload.',
            'file.required_without' => 'Choose at least one file to upload.',
            'files.*.mimes' => 'One of the files is not an allowed type (' . str_replace(',', ', ', $allowed) . ').',
            'files.*.file' => 'One of the files could not be uploaded. It may be larger than the server allows.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => array_values(array_unique($validator->errors()->all()))], 422);
            }

            return redirect()->to(route('tasks.show', $task->id) . '#tab-files')
                ->withErrors($validator, 'files')
                ->withInput($request->except(['files', 'file']));
        }

        $uploads = $request->hasFile('files') ? $request->file('files') : [$request->file('file')];
        $names = $request->hasFile('files') ? $request->input('names', []) : [$request->input('name')];

        foreach ($uploads as $index => $upload) {
            $original = $upload->getClientOriginalName();

            Auth::user()->files()->create([
                'name' => filled($names[$index] ?? null) ? $names[$index] : pathinfo($original, PATHINFO_FILENAME),
                'task_id' => $task->id,
                'path' => $upload->store('uploads', 'public'),
                'type' => $request->input('type') ?: $this->typeFor($upload->getClientOriginalExtension()),
            ]);
        }

        $count = count($uploads);
        $message = $count === 1 ? 'File uploaded successfully.' : $count . ' files uploaded successfully.';

        if ($request->expectsJson()) {
            $files = File::where('task_id', $task->id)->latest()->get();

            return response()->json([
                'message' => $message,
                'count' => $files->count(),
                'rows' => view('tasks.files.rows', compact('files', 'task'))->render(),
            ]);
        }

        return redirect()->to(route('tasks.show', $task->id) . '#tab-files')->with('success', $message);
    }

    /**
     * The file category used when the uploader leaves "File Type" on automatic.
     */
    private function typeFor(string $extension): string
    {
        return match (strtolower($extension)) {
            'jpeg', 'jpg', 'png', 'gif', 'svg' => 'image',
            'doc', 'docx', 'pdf' => 'docs',
            'txt' => 'txt',
            'html', 'css', 'js', 'php', 'java', 'c', 'cpp' => 'code',
            default => 'project',
        };
    }

    public function edit(Task $task, File $file)
    {
        $this->authorize('tasks-update', $task);
        $this->ensureTaskStoryIsEditable($task);

        return view('tasks.files.edit', compact('file', 'task'));
    }

    public function update(Request $request, Task $task, File $file)
    {
        $this->authorize('tasks-update', $task);
        $this->ensureTaskStoryIsEditable($task);

        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,doc,docx,pdf,txt,html,css,js,php,java,c,cpp',
            'type' => 'required|string|in:project,docs,txt,code,image',
        ]);

        $data = $request->only(['name', 'type']);

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($file->path);
            $data['path'] = $request->file('file')->store('uploads', 'public');
        }

        $file->update($data);

        return redirect()->to(route('tasks.show', $task->id) . '#tab-files')->with('success', 'File updated successfully.');
    }

    public function download(Task $task, File $file)
    {
        $this->authorize('tasks-show', $task);

        $this->ensureTaskFileRelation($task, $file);

        abort_unless(Storage::disk('public')->exists($file->path), 404);

        return Storage::disk('public')->download($file->path, $this->buildDownloadName($file));
    }

    public function destroy(Task $task,File $file)
    {
        $this->authorize('tasks-update', $task);
        $this->ensureTaskStoryIsEditable($task);

        $this->ensureTaskFileRelation($task, $file);
        Storage::disk('public')->delete($file->path);
        $file->delete();
        return redirect()->to(route('tasks.show', $task->id) . '#tab-files')->with('success', 'File deleted successfully.');
    }

    private function ensureTaskFileRelation(Task $task, File $file): void
    {
        abort_unless($file->task_id === $task->id, 404);
    }

    private function buildDownloadName(File $file): string
    {
        $storedExtension = pathinfo($file->path, PATHINFO_EXTENSION);
        $displayExtension = pathinfo($file->name, PATHINFO_EXTENSION);

        if ($storedExtension && !$displayExtension) {
            return $file->name.'.'.$storedExtension;
        }

        return $file->name;
    }
}
