<?php
namespace App\Http\Controllers;
use App\Models\{Project, Story, Task, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class StoryController extends Controller {
    private function accessible(Project $project): void { abort_unless(Auth::user()->hasRole('Admin') || $project->created_by===Auth::id() || $project->project_manager_id===Auth::id() || $project->members()->whereKey(Auth::id())->exists(),403); }
    public function create(Request $request) { $this->authorize('project-create'); $project=Project::findOrFail($request->project);$this->accessible($project);return view('stories.create',compact('project')); }
    public function store(Request $request) { $this->authorize('project-create'); $data=$request->validate(['project_id'=>'required|exists:projects,id','title'=>'required|max:255','description'=>'nullable|string','priority'=>'required|in:low,medium,high','due_date'=>'nullable|date']); $project=Project::findOrFail($data['project_id']);$this->accessible($project);$data['created_by']=Auth::id();$story=Story::create($data);return redirect()->route('stories.show',$story)->with('success','Story created successfully.'); }
    public function show(Request $request, Story $story) { $this->authorize('task-show'); $story->load('project');$this->accessible($story->project);$status=$request->get('status','all');$tasks=$story->tasks()->with('user')->when($status!=='all',fn($q)=>$q->where('status',$status))->when($request->filled('search'),fn($q)=>$q->where('title','like','%'.$request->search.'%'))->latest()->paginate(15)->withQueryString();return view('stories.show',compact('story','tasks','status')); }
    public function storeTask(Request $request, Story $story) { $this->authorize('task-create');$story->load('project');$this->accessible($story->project);$data=$request->validate(['title'=>'required|max:255','description'=>'nullable|string','user_id'=>'required|exists:users,id','due_date'=>'nullable|date','priority'=>'required|in:low,medium,high','estimated_hours'=>'nullable|numeric|min:0']);$data+=['client_id'=>$story->project->client_id,'project_id'=>$story->project_id,'story_id'=>$story->id,'status'=>'to_do'];Task::create($data);return back()->with('success','Task created successfully.'); }
}
