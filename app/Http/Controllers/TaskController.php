<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Chat;
use App\Models\File;
use App\Models\Story;
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
            ->where(function ($query) {
                $query->whereHas('project', fn ($clientQuery) => $clientQuery->whereNull('deleted_at'))
                    ->orWhereHas('deliveryProject');
            })
            ->when(
                $user->hasRole(\App\Support\Roles::CLIENT),
                fn ($query) => $query->where('client_id', $user->client?->id)
            )
            ->when(
                $user->hasRole(\App\Support\Roles::RESOURCE),
                fn ($query) => $query->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('created_by', $user->id))
            )
            ->when($request->get('client_id'), fn ($query, $clientId) => $query->where('client_id', $clientId))
            ->when($request->get('delivery_project_id'), fn ($query, $deliveryProjectId) => $query->where('project_id', $deliveryProjectId))
            ->when($request->get('project_id'), function ($query) {
                $query->where('client_id', request('project_id'));
            })
            ->when($request->get('search') != '', function ($query) {
                $query->where('title', 'like', '%' . request('search') . '%');
            })
            ->when($request->get('user'), function ($query) {
                $query->where('user_id', request('user'));
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
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
        ];

        $tasks = (clone $baseQuery)
            ->with([
                'project',
                'deliveryProject.client',
                'story',
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

        $users = User::ofRole(\App\Support\Roles::RESOURCE)->withTrashed()->orderBy('name')->get();
        $clients = Client::query()->orderBy('name')->get(['id', 'name']);
        $projectAssignments = DB::table('project_members')
            ->select('project_id', 'user_id')
            ->union(DB::table('tasks')
                ->select('project_id', 'user_id')
                ->whereNotNull('project_id')
                ->whereNotNull('user_id'))
            ->get()
            ->groupBy('project_id')
            ->map(fn ($assignments) => $assignments->pluck('user_id')->map(fn ($id) => (string) $id)->values());
        $filterProjects = Project::query()
            ->orderBy('name')
            ->get(['id', 'client_id', 'name'])
            ->map(fn (Project $filterProject) => [
                'id' => (string) $filterProject->id,
                'name' => $filterProject->name,
                'client_id' => (string) $filterProject->client_id,
                'members' => $projectAssignments->get($filterProject->id, collect()),
            ])
            ->values();
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
            'clients' => $clients,
            'filterProjects' => $filterProjects,
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
        $hasTrackedTime = $task->trackerHistories()
            ->where(fn ($query) => $query->where('elapsed_time', '>', 0)->orWhere('is_running', true))
            ->exists();

        $taskHistories = $this->timeLogHistories($task);

        // The open tracker row decides whether the timer is running; the clock starts from the server's own count.
        $runningHistory = TrackerHistory::where('task_id', $task->id)->where('is_running', true)->latest('start_time')->first();
        $runningSeconds = $runningHistory
            ? max(0, (int) Carbon::parse($runningHistory->start_time)->diffInSeconds(Carbon::now()))
            : null;

        return view('tasks.show', compact('task', 'chats', 'files', 'totalTime', 'taskHistories', 'runningSeconds', 'hasTrackedTime'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('tasks-update', $task);
        $this->ensureTaskStoryIsEditable($task);

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

    public function updatePriority(Request $request, Task $task)
    {
        $this->authorize('tasks-update', $task);
        $this->ensureTaskStoryIsEditable($task);

        $data = $request->validate(['priority' => 'required|in:low,medium,high']);
        $task->update($data);

        return back()->with('success', 'Task priority updated successfully.');
    }

    public function updateDueDate(Request $request, Task $task)
    {
        $this->authorize('tasks-update', $task);
        $this->ensureTaskStoryIsEditable($task);

        $data = $request->validate(['due_date' => 'nullable|date']);
        $oldDueDate = $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : null;
        $newDueDate = ($data['due_date'] ?? null) ?: null;

        DB::transaction(function () use ($task, $oldDueDate, $newDueDate) {
            $task->update(['due_date' => $newDueDate]);

            if ($oldDueDate !== $newDueDate) {
                $task->dueDateHistories()->create([
                    'old_due_date' => $oldDueDate,
                    'new_due_date' => $newDueDate,
                    'changed_by' => Auth::id(),
                ]);
            }
        });

        return back()->with('success', 'Task due date updated successfully.');
    }

    /**
     * Edit one finished session of a task. The page sends either
     *  - start_time + end_time: the duration is worked out from them, or
     *  - start_time + elapsed_time (or elapsed_time alone): the end is start + duration.
     * The saved values are sent back so the page shows exactly what is stored.
     */
    public function updateTime(Request $request, Task $task, TrackerHistory $trackerHistory)
    {
        $this->authorize('tasks-time-update');
        $this->ensureTaskStoryIsEditable($task);

        // the entry has to belong to the task whose page is being edited
        abort_unless((int) $trackerHistory->task_id === (int) $task->id, 404);

        if ($trackerHistory->is_running) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'elapsed_time' => 'This timer is still running. Stop it before editing its time.',
            ]);
        }

        $validated = $request->validate([
            'start_time' => 'nullable|required_with:end_time|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'elapsed_time' => 'nullable|integer|min:0',
        ], [
            'end_time.after_or_equal' => 'The end time cannot be before the start time.',
            'elapsed_time.integer' => 'Enter the duration as HH:MM:SS, for example 01:30:00.',
        ]);

        $hasTimes = $request->filled('start_time') && $request->filled('end_time');
        $hasDuration = $request->filled('elapsed_time');

        if (!$hasTimes && !$hasDuration) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'elapsed_time' => 'Enter a duration or a start and end time.',
            ]);
        }

        if ($hasTimes) {
            $start = Carbon::parse($validated['start_time']);
            $end = Carbon::parse($validated['end_time']);
            $elapsed = (int) $start->diffInSeconds($end);
        } else {
            $elapsed = (int) $validated['elapsed_time'];
            $start = $request->filled('start_time')
                ? Carbon::parse($validated['start_time'])
                : ($trackerHistory->start_time ? Carbon::parse($trackerHistory->start_time) : null);
            $end = $start?->copy()->addSeconds($elapsed);
        }

        $trackerHistory->start_time = $start;
        $trackerHistory->end_time = $end ?? $trackerHistory->end_time;
        $trackerHistory->elapsed_time = $elapsed;
        $trackerHistory->save();

        return response()->json([
            'success' => true,
            'start_time' => $trackerHistory->start_time ? Carbon::parse($trackerHistory->start_time)->format('Y-m-d H:i:s') : null,
            'end_time' => $trackerHistory->end_time ? Carbon::parse($trackerHistory->end_time)->format('Y-m-d H:i:s') : null,
            'elapsed_time' => $elapsed,
            'elapsed_formatted' => $this->_getTotalTime($elapsed),
            'total_time' => $this->_getTotalTrackerTime($task->user_id, $task->id),
        ]);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('tasks-update', $task);
        $this->ensureTaskStoryIsEditable($task);

        $request->validate(['status' => 'required|in:to_do,in_progress,completed,on_hold,in_review,pending,closed']);

        $task->status = $request->input('status');

        // Any status change stops the timer, so the open tracker row has to be closed with it.
        $timerStopped = TrackerHistory::where('task_id', $task->id)->where('is_running', true)->exists();
        $this->closeRunningHistories($task->id);
        $task->save();
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Task status updated successfully.',
                'status' => $task->status,
                'timer_stopped' => $timerStopped,
                'total_time' => $this->_getTotalTrackerTime($task->user_id, $task->id),
            ] + ($timerStopped ? $this->timeLogPayload($task) : []));
        }
        return back()->with('success', 'Task status updated successfully.');

        // return response()->json(['message' => 'Task status updated successfully.']);
    }
    public function sendChat(Request $request, $taskid)
    {
        $task = Task::findOrFail($taskid);
        $this->authorize('tasks-show', $task);
        $this->ensureTaskStoryIsEditable($task);

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

        return DB::transaction(function () use ($task, $user_id) {
            // Lock the assignee so two simultaneous starts cannot both pass the "nothing running" check.
            User::withTrashed()->whereKey($user_id)->lockForUpdate()->first();

            // The open tracker row is the source of truth for what is running.
            $running = TrackerHistory::where('user_id', $user_id)
                ->where('is_running', true)
                ->latest('start_time')
                ->first();

            if ($running && (int) $running->task_id === (int) $task->id) {
                // Already running on this task (second tab, stale page): just hand back the real clock.
                return response()->json([
                    'status' => true,
                    'seconds' => max(0, (int) Carbon::parse($running->start_time)->diffInSeconds(Carbon::now())),
                ]);
            }

            if ($running) {
                $other = Task::find($running->task_id);

                // what the task page's "Time Spent" counts (closed sessions) plus the session running right now
                $trackedSeconds = (int) TrackerHistory::where('task_id', $running->task_id)
                    ->where('user_id', $user_id)
                    ->where('is_running', false)
                    ->sum('elapsed_time')
                    + max(0, (int) Carbon::parse($running->start_time)->diffInSeconds(Carbon::now()));

                $dueDate = $other?->due_date ? Carbon::parse($other->due_date) : null;
                $estimatedHours = $other?->estimated_hours;

                return response()->json([
                    'status' => false,
                    'message' => 'Stop the timer of that task first to start this task.',
                    'running_task' => [
                        'id' => $running->task_id,
                        'title' => $other?->title ?? 'Another task',
                        'url' => route('tasks.show', $running->task_id),
                        'due_date' => $dueDate?->format('d F Y'),
                        'is_overdue' => $dueDate !== null && $dueDate->copy()->endOfDay()->isPast(),
                        'estimated' => $estimatedHours !== null
                            ? \App\Support\DurationFormatter::fromHours($estimatedHours)
                            : null,
                        'estimated_seconds' => $estimatedHours ? (int) round($estimatedHours * 3600) : null,
                        'tracked_seconds' => $trackedSeconds,
                    ],
                ]);
            }

            $taskHistory = new TrackerHistory();
            $taskHistory->task_id = $task->id;
            $taskHistory->user_id = $user_id;
            $taskHistory->start_time = Carbon::now();
            $taskHistory->is_running = true;
            $taskHistory->save();

            $statusChanged = $this->markInProgress($task);

            return response()->json(['status' => true, 'seconds' => 0] + ($statusChanged ? [
                'task_status' => 'in_progress',
                'task_status_label' => config('site.tasks.status.in_progress'),
            ] : []));
        });
    }

    public function stopTimer($taskid)
    {
        $task = Task::findOrFail($taskid);
        $this->authorizeTaskTimer($task);

        $user_id = $task->user_id;

        // Nothing open (already stopped, or the start never reached the server) is fine: there is simply nothing to close.
        $this->closeRunningHistories($task->id);

        $totalTime = $this->_getTotalTrackerTime($user_id, $taskid);
        return response()->json(['total_time' => $totalTime] + $this->timeLogPayload($task));
    }
    public function saveTime(Request $request, $taskid)
    {
        $task = Task::findOrFail($taskid);
        $this->authorizeTaskTimer($task);

        $taskHistory = TrackerHistory::query()
            ->where('task_id', $taskid)
            ->where('is_running', true)
            ->latest('start_time')
            ->first();

        // Stopped from somewhere else (another tab, a status change): tell the page instead of failing.
        if (!$taskHistory) {
            return response()->json(['running' => false, 'elapsed_time' => 0]);
        }

        $timeDifferenceInSeconds = max(0, (int) Carbon::parse($taskHistory->start_time)->diffInSeconds(Carbon::now()));
        // Keeps the open row current, so little is lost if the timer is never stopped.
        $taskHistory->update([
            'elapsed_time' => $timeDifferenceInSeconds,
        ]);
        return response()->json([
            'running' => true,
            'message' => 'Time saved successfully',
            'elapsed_time' => $timeDifferenceInSeconds
        ]);
    }

    public function mnanualTrack(Request $request, Task $task)
    {
        $this->authorize('tasks-track-manually', $task);
        $this->authorizeTaskTimer($task);

        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after:start',
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
        $task_ids = is_array($validated['task_ids']) ? $validated['task_ids'] : [$validated['task_ids']];

        if (Task::whereIn('id', $task_ids)->whereHas('story', fn ($query) => $query->where('status', 'completed'))->exists()) {
            return response()->json(['message' => 'This story is completed. Reopen it before changing its tasks.'], 403);
        }

        Task::whereIn('id', $task_ids)->update(['status' => 'completed']);

        // Completing a task stops any timer still running on it
        foreach ($task_ids as $task_id) {
            $this->closeRunningHistories((int) $task_id);
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
        abort_unless(Auth::user()->hasRole(\App\Support\Roles::ADMIN), 403, 'Only an admin can delete tasks.');
        $this->ensureTaskStoryIsEditable($task, true);

        $hasTrackedTime = $task->trackerHistories()
            ->where(fn ($query) => $query->where('elapsed_time', '>', 0)->orWhere('is_running', true))
            ->exists();

        if ($hasTrackedTime) {
            return back()->with('error', 'This task cannot be deleted because time has already been tracked.');
        }

        $storyId = $task->story_id;
        $task->delete();

        return $storyId
            ? redirect()->route('stories.show', $storyId)->with('success', 'Task deleted successfully.')
            : redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    /**
     * Starting work on a task moves the task, and the story it belongs to, to In Progress.
     * A closed task is left alone. Returns whether the task's own status changed.
     */
    private function markInProgress(Task $task): bool
    {
        if ($task->status === 'closed') {
            return false;
        }

        if ($task->story_id) {
            Story::whereKey($task->story_id)
                ->where(fn ($query) => $query->whereNull('status')->orWhere('status', '!=', 'in_progress'))
                ->update(['status' => 'in_progress']);
        }

        if ($task->status === 'in_progress') {
            return false;
        }

        $task->update(['status' => 'in_progress']);

        return true;
    }

    /**
     * The finished sessions listed in the task's Time Log tab.
     */
    private function timeLogHistories(Task $task)
    {
        return TrackerHistory::where('user_id', $task->user_id)
            ->where('task_id', $task->id)
            ->where('is_running', false)
            ->orderBy('id')
            ->get();
    }

    /**
     * The Time Log rows as HTML, so the page can refresh the tab after a timer stops without a reload.
     */
    private function timeLogPayload(Task $task): array
    {
        $taskHistories = $this->timeLogHistories($task);

        return [
            'time_log_html' => view('tasks.partials.time-log-rows', [
                'taskHistories' => $taskHistories,
                'canEditTime' => Auth::user()->can('tasks-time-update'),
            ])->render(),
            'time_log_count' => $taskHistories->count(),
        ];
    }

    /**
     * Close every open tracker row of a task, each measured from its own start time.
     */
    private function closeRunningHistories(int $taskId): void
    {
        $now = Carbon::now();

        TrackerHistory::where('task_id', $taskId)->where('is_running', true)->get()
            ->each(fn (TrackerHistory $history) => $history->update([
                'end_time' => $now,
                'is_running' => false,
                'elapsed_time' => max(0, (int) Carbon::parse($history->start_time)->diffInSeconds($now)),
            ]));
    }

    /** Only the assigned user may start, stop, save, or manually log time. */
    private function authorizeTaskTimer(Task $task): void
    {
        $this->ensureTaskStoryIsEditable($task);
        abort_unless((int) $task->user_id === (int) Auth::id(), 403);
    }
}
