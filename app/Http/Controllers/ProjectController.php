<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index(Request $request)
{
    $this->authorize('project-show');
    $user = Auth::user();
    
    $projectQuery = $user->hasRole('Admin') ? $user->projects() : $user->projectMembers();

    $projects = $projectQuery->when($request->type == 'archived', function ($query) {
        return $query->onlyTrashed();  
    });

    $projects = $projects->withCount([
        'tasks as to_do_tasks' => function ($query) {
            $query->where('status', 'to_do');
        },
        'tasks as in_progress_tasks' => function ($query) {
            $query->where('status', 'in_progress');
        },
        'tasks as in_review_tasks' => function ($query) {
            $query->where('status', 'in_review');
        },
        
        'tasks as completed_tasks' => function ($query) {
            $query->where('status', 'completed');
        }
    ]);

    $projects = $projects->with('users')  ->latest()
    ->paginate(50)
    ->withQueryString();

    return view('projects.index', compact('projects'));
}

    public function create()
    {
        $this->authorize('project-create');

        $employees = User::role('Employee')->get();
        return view('projects.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $this->authorize('project-create');

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'required|in:not_started,in_progress,completed',
            'budget' => 'nullable|numeric',
            'employee' => 'required'
        ]);

        $project = Auth::user()->projects()->create($request->all());
        $project->teamProjects()->attach($request->employee);
        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }


    public function show(Project $project)
    {
        $this->authorize('project-show');
        $teamMembers = $project->users()->get();
        $users = User::all();
        return view('projects.show', compact('project', 'teamMembers', 'users'));
    }
    public function edit(Project $project)
    {
        $this->authorize('project-edit');

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('project-edit');

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'required|in:not_started,in_progress,completed',
            'budget' => 'nullable|numeric',
        ]);

        $project->update($request->all());

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('project-delete');

        DB::table('project_teams')->where('project_id', $project->id)->delete();

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }



    public function addMember(Request $request)
    {
        $this->authorize('project-edit');

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $project = Project::find($request->project_id);
        $project->teamProjects()->attach($request->user_id);
        return redirect()->back()->with('success', 'User added successfully.');
    }
    public function removeMember(Request $request)
{
    $this->authorize('project-edit');

    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'user_id' => 'required|exists:users,id',
    ]);

    $project = Project::find($request->project_id);
    $project->teamProjects()->detach($request->user_id);

    return redirect()->back()->with('success', 'User removed successfully.');
}
    public function getProjectUsers($projectId)
    {
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $users = $project->sortedUsers();
        return response()->json($users);
    }
    public function restore($projectId)
{
    $record = Project::withTrashed()->findOrFail($projectId);
    $record->restore();

    return redirect()->back()->with('success', 'Project restored successfully.');
}
}
