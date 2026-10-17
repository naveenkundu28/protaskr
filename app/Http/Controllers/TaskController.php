<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Chat;
use App\Models\File;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Carbon\CarbonInterval;
use App\Exports\TaskExport;
use App\Models\Client;
use App\Traits\TrackerTrait;
use Illuminate\Http\Request;
use App\Models\TrackerHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{
    use TrackerTrait;
    public function index(Request $request)
    {
        $this->authorize('tasks-access');
        $user = Auth::user();

        $tabStatuses = [
            'active' => ['to_do', 'in_progress'],
            'to_do' => ['to_do'],
            'in_progress' => ['in_progress'],
            'in_review' => ['in_review'],
            'on_hold' => ['on_hold'],
            'completed' => ['completed'],
        ];

        $tab = $request->get('status') ?: $request->get('tab', 'active');

        $baseQuery = Task::query()
            ->whereHas('project', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->when(
                $user->hasRole(\App\Support\Roles::CLIENT),
                fn ($query) => $query->where('client_id', $user->client?->id)
            )
            ->when(
                $user->hasRole(\App\Support\Roles::RESOURCE),
                fn ($query) => $query->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('created_by', $user->id))
            )
            ->when($request->get('project_id'), function ($query) {
                $query->where('client_id', request('project_id'));
            })
            ->when($request->get('search') != '', function ($query) {
                $query->where('title', 'like', '%' . request('search') . '%');
            })
            ->when($request->get('user'), function ($query) {
                $query->where('user_id', request('user'));
            })
            ->when($request->get('cdate'), function ($query) {
                $query->whereDate('created_at', request('cdate'));
            })
            ->when($request->get('duedate'), function ($query) {
                $query->whereDate('due_date', request('duedate'));
            });

        $tabCounts = [
            'active' => (clone $baseQuery)->whereIn('status', $tabStatuses['active'])->count(),
            'to_do' => (clone $baseQuery)->where('status', 'to_do')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'in_review' => (clone $baseQuery)->where('status', 'in_review')->count(),
            'on_hold' => (clone $baseQuery)->where('status', 'on_hold')->count(),
        ];

        $tasks = (clone $baseQuery)
            ->with([
                'project',
                'user' => function ($query) {
                    $query->withTrashed();
                }
            ])
            ->when($request->get('status') != '', function ($query) {
                $query->where('status', request('status'));
            })
            ->when(!$request->get('status'), function ($query) use ($tabStatuses, $tab) {
                $statuses = $tabStatuses[$tab] ?? $tabStatuses['active'];
                $query->whereIn('status', $statuses);
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $users = User::ofRole(\App\Support\Roles::RESOURCE)->withTrashed()->get();
        $project = null;
        if ($request->get('project_id')) {
            // $project = Client::with('users')->find($request->get('project_id'));

            $project = Client::with(['users' => function ($query) {
                $query->withTrashed();
            }])->find($request->get('project_id'));
        }

        return view('tasks.index')->with([
            'project' => $project,
            'tasks' => $tasks,
            'users' => $users,
            'currentTab' => $tab,
            'tabCounts' => $tabCounts,
        ]);
    }


    public function store(Request $request)
    {
        $this->authorize('tasks-create');

        $validated =  $request->validate([
            'client_id' => 'required|numeric',
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:to_do,in_progress,completed,on_hold,in_review,pending,closed',
        ]);

        Task::create($validated + ['created_by' => auth()->id()]);

        return back()->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $this->authorize('tasks-show', $task);

        $task->load('deliveryProject.members', 'story', 'dueDateHistories');

        $user_id = $task->user_id;
        $files = File::where('task_id', $task->id)->latest()->get();
        $chats = Chat::with('user')->where('task_id', $task->id)->get();

        $totalTime = $this->_getTotalTrackerTime($user_id, $task->id);

        $taskHistories = TrackerHistory::where('user_id', $user_id)->where('task_id', $task->id)->where('is_running', false)->get();

        return view('tasks.show', compact('task', 'chats', 'files', 'totalTime', 'taskHistories'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('tasks-update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:to_do,in_progress,completed,on_hold,in_review,pending,closed',
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::exists('project_members', 'user_id')
                    ->where('project_id', $task->project_id),
            ],
        ]);

        $oldDueDate = $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : null;
        $newDueDate = array_key_exists('due_date', $validated)
            ? ($validated['due_date'] ?: null)
            : $oldDueDate;

        DB::transaction(function () use ($task, $validated, $oldDueDate, $newDueDate) {
            $task->update($validated);

            if ($oldDueDate !== $newDueDate) {
                $task->dueDateHistories()->create([
                    'old_due_date' => $oldDueDate,
                    'new_due_date' => $newDueDate,
                    'changed_by' => Auth::id(),
                ]);
            }
        });

        return back()->with('success', 'Task updated successfully.');
    }

    public function updateTime(Request $request, $trackerId)
    {
        $this->authorize('tasks-time-update');

        $request->validate([
            'elapsed_time' => 'required|integer|min:0',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
        ]);

        $history = TrackerHistory::findOrFail($trackerId);

        if ($request->filled('start_time')) {
            $history->start_time = Carbon::parse($request->start_time);
        }

        if ($request->filled('end_time')) {
            $history->end_time = Carbon::parse($request->end_time);
        }

        if ($request->filled('start_time') && $request->filled('end_time')) {
            $history->elapsed_time = Carbon::parse($request->end_time)
                ->diffInSeconds(Carbon::parse($request->start_time));
        } else {
            $history->elapsed_time = $request->elapsed_time;
        }
        $history->save();

        return response()->json([
            'success' => true
        ]);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('tasks-update', $task);

        $request->validate(['status' => 'required|in:to_do,in_progress,completed,on_hold,in_review,pending,closed']);

        $task->status = $request->input('status');
        $task->is_running = false;
        $task->end_time = Carbon::now();
        $task->elapsed_time = 0;
        if ($task->status == 'completed') {
            $taskHistory = TrackerHistory::where('task_id', $task->id)
                ->where('is_running', true);

            if ($taskHistory) {
                $taskhistorydata = $taskHistory->first();
                if ($taskhistorydata) {
                    $startTime = Carbon::parse($taskhistorydata->start_time);
                    $endTime = Carbon::now();

                    $timeDifferenceInSeconds = $endTime->diffInSeconds($startTime);
                    $taskHistory->update([
                        'end_time' => $endTime,
                        'is_running' => false,
                        'elapsed_time' => $timeDifferenceInSeconds,
                    ]);
                }
            }
        }
        $task->save();
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Task status updated successfully.', 'status' => $task->status]);
        }
        return back()->with('success', 'Task status updated successfully.');

        // return response()->json(['message' => 'Task status updated successfully.']);
    }
    public function sendChat(Request $request, $taskid)
    {
        $task = Task::findOrFail($taskid);
        $this->authorize('tasks-show', $task);

        $userId = auth()->id();

        $content = trim((string) $request->message);

        if (mb_strlen($content) > 5000) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['The message is too long. Keep it under 5000 characters.']], 422);
            }

            return back()
                ->withErrors(['message' => 'The message is too long. Keep it under 5000 characters.'])
                ->withInput();
        }

        if ($content === '') {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['Write a message before sending.']], 422);
            }

            return back()
                ->withErrors(['message' => 'Message is required'])
                ->withInput();
        }
        $savechat = new Chat();
        $savechat->user_id = $userId;
        $savechat->task_id = $taskid;
        // stored as safe HTML: the text is escaped, then the light formatting is applied
        $savechat->message = \App\Support\ChatMarkdown::toHtml($content);

        $savechat->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => view('tasks.chat-message', ['chat' => $savechat])->render(),
                'count' => Chat::where('task_id', $taskid)->count(),
            ]);
        }

        return redirect()->to(route('tasks.show', $taskid) . '#tab-chat')->with('success', 'Message Sent');
    }
    public function startTimer($taskid)
    {
        $task = Task::findOrFail($taskid);
        $this->authorizeTaskTimer($task);

        $user_id = $task->user_id;
        $taskcheck = Task::where('user_id', $user_id)->where('is_running', true)->first();
        if ($taskcheck) {
            return response()->json(['status' => false, 'message' => 'Stop "' . $taskcheck->title . '" timer first.']);
        }
        if ($task->is_running == false) {
            if ($task->elapsed_time == 0) {
                $task->update([
                    'start_time' => Carbon::now(),
                    'is_running' => true,
                ]);
            } else {
                $task->update([
                    'is_running' => true,
                ]);
            }

            $taskHistory = new TrackerHistory();
            $taskHistory->task_id = $taskid;
            $taskHistory->user_id = $user_id;
            $taskHistory->start_time = Carbon::now();
            $taskHistory->is_running = true;
            $taskHistory->save();
            return response()->json(['status' => true]);
        }
    }

    public function stopTimer($taskid)
    {
        $task = Task::findOrFail($taskid);
        $this->authorizeTaskTimer($task);

        $user_id = $task->user_id;
        $taskHistory = TrackerHistory::where('user_id', $user_id)->where('task_id', $taskid)
            ->where('is_running', true);
        $taskhistorydata = $taskHistory->first();

        // Nothing running (already stopped, or the start never reached the server): just make sure the task is marked stopped.
        if ($taskhistorydata) {
            $startTime = Carbon::parse($taskhistorydata->start_time);
            $endTime = Carbon::now();

            $timeDifferenceInSeconds = $endTime->diffInSeconds($startTime);
            $taskHistory->update([
                'end_time' => Carbon::now(),
                'is_running' => false,
                'elapsed_time' => $timeDifferenceInSeconds,
            ]);
        }
        $task->update([
            'end_time' => Carbon::now(),
            'elapsed_time' => 0,
            'is_running' => false,
        ]);

        $totalTime = $this->_getTotalTrackerTime($user_id, $taskid);
        return response()->json(['total_time' => $totalTime]);
    }
    public function saveTime(Request $request, $taskid)
    {
        $task = Task::findOrFail($taskid);
        $this->authorizeTaskTimer($task);

        $taskHistory = TrackerHistory::query()
            ->where('task_id', $taskid)
            ->where('is_running', true)
            ->first();
        $startTime = Carbon::parse($taskHistory->start_time);
        $endTime = Carbon::now();

        $timeDifferenceInSeconds = $endTime->diffInSeconds($startTime);
        $taskHistory->update([
            'elapsed_time' => $timeDifferenceInSeconds,
        ]);
        $task->update([
            'elapsed_time' => $timeDifferenceInSeconds,
        ]);
        return response()->json([
            'message' => 'Time saved successfully',
            'elapsed_time' => $timeDifferenceInSeconds
        ]);
    }

    public function mnanualTrack(Request $request, Task $task)
    {
        $this->authorize('tasks-track-manually', $task);
        $this->authorizeTaskTimer($task);

        $request->validate([
            'start' => 'required',
            'end' => 'required',
        ]);
        $user_id = auth()->id();
        $startTime = Carbon::parse($request->start);
        $endTime = Carbon::parse($request->end);
        $timeDifferenceInSeconds = $endTime->diffInSeconds($startTime);
        $trackHistory = new TrackerHistory();
        $trackHistory->user_id = $user_id;
        $trackHistory->task_id = $task->id;
        $trackHistory->start_time = $startTime;
        $trackHistory->end_time = $endTime;
        $trackHistory->elapsed_time = $timeDifferenceInSeconds;
        $trackHistory->save();
        return redirect()->to(route('tasks.show', $task->id) . '#tab-time-log')->with('success', 'Time added successfully.');
    }
    public function markComplete(Request $request)
    {
        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
        ]);
        $this->authorize('tasks-bulk-complete');
        $task = Task::whereIn('id', $validated['task_ids'])
            ->update([
                'status' => 'completed',
                'end_time' => Carbon::now(),
                'is_running' => false,
                'elapsed_time' => 0
            ]);
        $task_ids = is_array($validated['task_ids']) ? $validated['task_ids'] : [$validated['task_ids']];

        // Fetch the records that are currently running
        $taskHistories = TrackerHistory::whereIn('task_id', $task_ids)
            ->where('is_running', true)
            ->get();

        // Loop through each task history and calculate the elapsed time
        foreach ($taskHistories as $taskHistory) {
            // Calculate elapsed time in seconds
            $startTime = Carbon::parse($taskHistory->start_time);
            $endTime = Carbon::now();
            $elapsedTimeInSeconds = $endTime->diffInSeconds($startTime);

            // Update the individual task history with calculated values
            $taskHistory->update([
                'end_time' => $endTime,
                'is_running' => false,
                'elapsed_time' => $elapsedTimeInSeconds,
            ]);
        }
        return response()->json(['message' => 'Tasks status updated successfully!']);
    }
    public function export(Request $request)
    {
        $this->authorize('tasks-export');

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $projectid = $request->input('project_id');
        return Excel::download(new TaskExport($fromDate, $toDate, $projectid), 'tasks.xlsx');
    }
    public function destroy(Task $task)
    {
        $this->authorize('tasks-delete', $task);
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    /**
     * Only the assignee may log time against a task. Administrators retain their
     * existing task-management access.
     */
    private function authorizeTaskTimer(Task $task): void
    {
        abort_unless(
            Auth::user()->hasRole(\App\Support\Roles::ADMIN) || $task->user_id === Auth::id(),
            403
        );
    }
}
