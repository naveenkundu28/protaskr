<?php
namespace App\Http\Controllers;
use App\Models\{Project, Story, Task, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class StoryController extends Controller {
    public function create(Request $request) { $this->authorize('projects-stories-create'); $project=Project::findOrFail($request->project);return view('stories.create',compact('project')); }
    public function store(Request $request) { $this->authorize('projects-stories-create'); $data=$request->validate(['project_id'=>'required|exists:projects,id','title'=>'required|max:255','description'=>'nullable|string','acceptance_criteria'=>'nullable|string','story_points'=>'nullable|integer|min:0|max:65535','priority'=>'required|in:low,medium,high','due_date'=>'nullable|date']); $project=Project::findOrFail($data['project_id']);$data['created_by']=Auth::id();$story=Story::create($data);return redirect()->route('stories.show',$story)->with('success','Story created successfully.'); }
    public function update(Request $request, Story $story) { $this->authorize('projects-stories-update'); $this->ensureStoryIsEditable($story); $story->load('project'); $data=$request->validate(['title'=>'required|max:255','description'=>'nullable|string','acceptance_criteria'=>'nullable|string','story_points'=>'nullable|integer|min:0|max:65535','priority'=>'required|in:low,medium,high','due_date'=>'nullable|date']); $oldDueDate=optional($story->due_date)->format('Y-m-d'); $newDueDate=array_key_exists('due_date',$data) ? ($data['due_date'] ?: null) : $oldDueDate; DB::transaction(function () use ($story, $data, $oldDueDate, $newDueDate) { $story->update($data); if ($oldDueDate !== $newDueDate) { $story->dueDateHistories()->create(['old_due_date'=>$oldDueDate,'new_due_date'=>$newDueDate,'changed_by'=>Auth::id()]); } }); return redirect()->route('projects.show', ['project' => $story->project, 'tab' => 'stories'])->with('success', 'Story updated successfully.'); }
    public function updateStatus(Request $request, Story $story) { $this->authorize('projects-stories-update'); $data=$request->validate(['status'=>'required|in:to_do,in_progress,in_review,completed,blocked']); if ($data['status'] === 'completed' && $story->tasks()->where('status', '!=', 'completed')->exists()) { return back()->with('completion_error', 'You can mark this story as complete only after all of its tasks are completed.'); } $story->update($data); return back()->with('success', 'Story status updated successfully.'); }
    public function updatePriority(Request $request, Story $story) { $this->authorize('projects-stories-update'); $this->ensureStoryIsEditable($story); $data=$request->validate(['priority'=>'required|in:low,medium,high']); $story->update($data); return back()->with('success', 'Story priority updated successfully.'); }
    public function updateDueDate(Request $request, Story $story) { $this->authorize('projects-stories-update'); $this->ensureStoryIsEditable($story); $data=$request->validate(['due_date'=>'nullable|date']); $oldDueDate=optional($story->due_date)->format('Y-m-d'); $newDueDate=($data['due_date'] ?? null) ?: null; DB::transaction(function () use ($story, $newDueDate, $oldDueDate) { $story->update(['due_date'=>$newDueDate]); if ($oldDueDate !== $newDueDate) { $story->dueDateHistories()->create(['old_due_date'=>$oldDueDate,'new_due_date'=>$newDueDate,'changed_by'=>Auth::id()]); } }); return back()->with('success', 'Story due date updated successfully.'); }
    public function destroy(Story $story) { $this->authorize('projects-stories-delete'); $this->ensureStoryIsEditable($story); $story->load('project'); $project=$story->project; $story->delete(); return redirect()->route('projects.show', ['project' => $project, 'tab' => 'stories'])->with('success', 'Story deleted successfully.'); }
    public function show(Request $request, Story $story) {
        $this->authorize('tasks-show');

        $story->load('project.members', 'project.client', 'project.creator', 'dueDateHistories');
        $tab = $request->get('tab', $request->get('status', 'all'));
        $tab = in_array($tab, ['overview', 'all', 'to_do', 'in_progress', 'in_review', 'completed', 'on_hold'], true) ? $tab : 'all';
        $status = $tab === 'overview' ? 'all' : $tab;

        $tasks = $story->tasks()
            ->with('user')
            ->withSum('trackerHistories as tracked_seconds', 'elapsed_time')
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // tasks on this page that have a timer running right now (an open tracker row)
        $runningTaskIds = DB::table('tracker_histories')
            ->whereIn('task_id', $tasks->pluck('id'))
            ->where('is_running', true)
            ->pluck('task_id')
            ->all();

        $trackedSeconds = (int) DB::table('tracker_histories')
            ->join('tasks', 'tracker_histories.task_id', '=', 'tasks.id')
            ->where('tasks.story_id', $story->id)
            ->sum('tracker_histories.elapsed_time');

        $taskStatuses = $story->tasks()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $overview = [
            'task_statuses' => $taskStatuses,
            'task_total' => $taskStatuses->sum(),
            'estimated_hours' => (float) $story->tasks()->sum('estimated_hours'),
            'tracked_seconds' => $trackedSeconds,
            'team_members' => $story->project->members->unique('id')->values(),
        ];

        return view('stories.show', compact('story', 'tasks', 'status', 'tab', 'overview', 'runningTaskIds'));
    }
    public function storeTask(Request $request, Story $story) { $this->authorize('tasks-create'); $this->ensureStoryIsEditable($story); $story->load('project');$data=$request->validate(['title'=>'required|max:255','description'=>'nullable|string','user_id'=>'required|exists:users,id','due_date'=>'nullable|date','priority'=>'required|in:low,medium,high','estimated_hours'=>'nullable|numeric|min:0|max:4']);$data+=['client_id'=>$story->project->client_id,'project_id'=>$story->project_id,'story_id'=>$story->id,'status'=>'to_do','created_by'=>Auth::id()];Task::create($data);return back()->with('success','Task created successfully.'); }
}
