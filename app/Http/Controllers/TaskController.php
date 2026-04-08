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
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{
    use TrackerTrait;
    public function index(Request $request)
    {
        $this->authorize('task-show');
        $user = Auth::user();
        $tasks = Task::query()
            // ->with('project', 'user')
            ->with([
                'project',
                'user' => function ($query) {
                    $query->withTrashed();
                }
            ])
            ->whereHas('project', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->when(!$request->get('project_id') && !$user->hasRole('Admin'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($request->get('project_id'), function ($query) {
                $query->where('client_id', request('project_id'));
            })
            ->when($request->get('search') != '', function ($query) {
                $query->where('title', 'like', '%' . request('search') . '%');
            })
            ->when($request->get('status') != '', function ($query) {
                $query->where('status', request('status'));
            })
            ->when(!$request->get('status'), function ($query) {
                $query->where('status', '!=', 'completed');
            })
            ->when($request->get('user'), function ($query) {
                $query->where('user_id', request('user'));
            })
            ->when($request->get('cdate'), function ($query) {
                $query->whereDate('created_at', request('cdate'));
            })
            ->when($request->get('duedate'), function ($query) {
                $query->whereDate('due_date', request('duedate'));
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $users = User::role('employee')->withTrashed()->get();
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
            'users' => $users
        ]);
    }


    public function store(Request $request)
    {
        $this->authorize('task-create');

        $validated =  $request->validate([
            'client_id' => 'required|numeric',
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:to_do,in_progress,completed,blocked,in_review,pending,closed',
        ]);

        Task::create($validated);

        return back()->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $this->authorize('task-show');
        $this->authorizeTaskAccess($task);

        $user_id = $task->user_id;
        $files = File::where('task_id', $task->id)->latest()->get();
        $chats = Chat::with('user')->where('task_id', $task->id)->get();

        $totalTime = $this->_getTotalTrackerTime($user_id, $task->id);

        $taskHistories = TrackerHistory::where('user_id', $user_id)->where('task_id', $task->id)->where('is_running', false)->get();

        return view('tasks.show', compact('task', 'chats', 'files', 'totalTime', 'taskHistories'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('task-edit');
        $this->authorizeTaskAccess($task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:to_do,in_progress,completed,blocked,in_review,pending,closed',
        ]);

        $task->update($validated);

        return back()->with('success', 'Task updated successfully.');
    }

    public function updateTime(Request $request, $trackerId)
    {
        $this->authorize('task-edit');

        $request->validate([
            'elapsed_time' => 'required|integer|min:0'
        ]);

        $history = TrackerHistory::findOrFail($trackerId);

        $history->elapsed_time = $request->elapsed_time;
        $history->save();

        return response()->json([
            'success' => true
        ]);
    }

    public function updateStatus(Request $request, Task $task)
    {
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
        return back()->with('success', 'Task status updated successfully.');

        // return response()->json(['message' => 'Task status updated successfully.']);
    }
    public function sendChat(Request $request, $taskid)
    {
        $this->authorize('task-show');
        $task = Task::findOrFail($taskid);
        $this->authorizeTaskAccess($task);

        $userId = auth()->id();

        $content = html_entity_decode($request->message);
        $content = trim(preg_replace('/\s+/u', '', strip_tags($content)));
        // $content = trim(strip_tags($request->message));
        if (empty($content)) {
            return back()
                ->withErrors(['message' => 'Message is required'])
                ->withInput();
        }
        // $request->validate([
        //     'message' => 'required'
        // ]);
        $savechat = new Chat();
        $savechat->user_id = $userId;
        $savechat->task_id = $taskid;
        $savechat->message = $request->message;

        $savechat->save();
        return back()->with('success', 'Message Sent');
    }
    public function startTimer($taskid)
    {
        $task = Task::findOrFail($taskid);
        $this->authorizeTaskAccess($task);

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
        $this->authorizeTaskAccess($task);

        $user_id = $task->user_id;
        $taskHistory = TrackerHistory::where('user_id', $user_id)->where('task_id', $taskid)
            ->where('is_running', true);
        $taskhistorydata = $taskHistory->first();
        $startTime = Carbon::parse($taskhistorydata->start_time);
        $endTime = Carbon::now();

        $timeDifferenceInSeconds = $endTime->diffInSeconds($startTime);
        $taskHistory->update([
            'end_time' => Carbon::now(),
            'is_running' => false,
            'elapsed_time' => $timeDifferenceInSeconds,
        ]);
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
        $this->authorizeTaskAccess($task);

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
        $this->authorizeTaskAccess($task);
        $task = $task->load('user');
        if ($task->user->manual == true) {

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
            return redirect()->route('tasks.show', $task->id)->with('success', 'Time added successfully.');
        } else {
            return redirect()->route('tasks.show', $task->id)->with('success', 'You are not authorized to track time manually.');
        }
    }
    public function markComplete(Request $request)
    {
        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
        ]);
        $this->authorize('mark-task-complete');
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
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $projectid = $request->input('project_id');
        return Excel::download(new TaskExport($fromDate, $toDate, $projectid), 'tasks.xlsx');
    }
    public function destroy(Task $task)
    {
        $this->authorizeTaskAccess($task);
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    private function authorizeTaskAccess(Task $task): void
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            return;
        }

        $task->loadMissing('project');

        $isAssignedToClient = $task->project
            && $task->project->allUsers()->where('users.id', $user->id)->exists();

        abort_unless($isAssignedToClient, 403);
    }
}
