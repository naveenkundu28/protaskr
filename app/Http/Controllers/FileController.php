<?php
namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Task;

class FileController extends Controller
{
    public function index(Task $task)
    {
        $files = Auth::user()->files()->where('task_id', $task->id)->latest()->get();
        return view('files.index', compact('files', 'task'));
    }

    public function create(Task $task)
    {
        return view('files.create', compact('task'));
    }

    public function show(Task $task, File $file)
    {
        $this->ensureTaskFileRelation($task, $file);

        abort_unless(Storage::disk('public')->exists($file->path), 404);

        return Storage::disk('public')->response($file->path, $this->buildDownloadName($file));
    }

    public function store(Request $request, Task $task)
    {
        dd($request->all());
		$request->validate([
            'name' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,doc,docx,pdf,txt,html,css,js,php,java,c,cpp',
            'type' => 'required|string|in:project,docs,txt,code,image',
        ]);

        if (!$request->hasFile('file')) {
            return back()->with('error', 'No file uploaded.');
        }
        $path = $request->file('file')->store('uploads', 'public');

        Auth::user()->files()->create([
            'name' => $request->name,
            'task_id' => $task->id,
            'path' => $path,
            'type' => $request->type,
        ]);

        return redirect()->route('task.files.index', $task->id)->with('success', 'File uploaded successfully.');
    }

    public function edit(Task $task, File $file)
    {
        return view('files.edit', compact('file', 'task'));
    }

    public function update(Request $request, Task $task, File $file)
    {
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

        return redirect()->route('task.files.index', $task->id)->with('success', 'File updated successfully.');
    }

    public function download(Task $task, File $file)
    {
        $this->ensureTaskFileRelation($task, $file);

        abort_unless(Storage::disk('public')->exists($file->path), 404);

        return Storage::disk('public')->download($file->path, $this->buildDownloadName($file));
    }

    public function destroy(Task $task,File $file)
    {
        $this->ensureTaskFileRelation($task, $file);
        Storage::disk('public')->delete($file->path);
        $file->delete();
        return redirect()->route('task.files.index', $task->id)->with('success', 'File deleted successfully.');
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
