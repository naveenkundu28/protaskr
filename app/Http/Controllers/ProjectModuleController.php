<?php
namespace App\Http\Controllers;
use App\Models\{Client, Project, ProjectFile, ProjectFolder, Story, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class ProjectModuleController extends Controller
{
    private function data(Request $request): array
    {
        $data = $request->validate([
            'client_id' => 'nullable|required_if:project_type,external|exists:clients,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'project_type' => 'required|in:internal,external',
            'priority' => 'required|in:low,medium,high',
            'budget_hours' => 'nullable|numeric|min:0',
            'project_manager_id' => 'nullable|exists:users,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
            'status' => 'required|in:active,completed,on_hold,not_started',
        ]);

        // An internal project must not keep a client selected before the type changed.
        if ($data['project_type'] === 'internal') {
            $data['client_id'] = null;
        }

        return $data;
    }

    public function index(Request $request)
    {
        $this->authorize('projects-access');

        $status = $request->get('status', 'all');
        $projects = Project::with(['client', 'manager'])
            ->withCount('tasks')
            ->when(
                Auth::user()->hasRole(\App\Support\Roles::CLIENT),
                fn ($q) => $q->whereHas('client', fn ($c) => $c->where('profile_user_id', Auth::id()))
            )
            ->when(
                Auth::user()->hasRole(\App\Support\Roles::RESOURCE),
                fn ($q) => $q->whereHas('members', fn ($m) => $m->where('users.id', Auth::id()))
            )
            ->when(
                $status !== 'all',
                fn ($q) => $q->where('status', $status)
            )
            ->when(
                $request->filled('client_id'),
                fn ($q) => $q->where('client_id', $request->integer('client_id'))
            )
            ->when(
                $request->filled('assigned_user_id'),
                fn ($q) => $q->whereHas(
                    'members',
                    fn ($m) => $m->where('users.id', $request->integer('assigned_user_id'))
                )
            )
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where(function ($x) use ($request) {
                    $x->where('name', 'like', '%' . $request->search . '%')
                        ->orWhereHas('client', function ($c) use ($request) {
                            $c->where(
                                'name',
                                'like',
                                '%' . $request->search . '%'
                            );
                        });
                })
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $counts = Project::selectRaw(
            "count(*) total,
             sum(status = 'active') active,
             sum(status = 'completed') completed,
             sum(status = 'on_hold') on_hold"
        )->when(
            Auth::user()->hasRole(\App\Support\Roles::CLIENT),
            fn ($q) => $q->whereHas('client', fn ($c) => $c->where('profile_user_id', Auth::id()))
        )->when(
            Auth::user()->hasRole(\App\Support\Roles::RESOURCE),
            fn ($q) => $q->whereHas('members', fn ($m) => $m->where('users.id', Auth::id()))
        )->first();

        $formProject = new Project();

        if ($request->filled('edit')) {
            $this->authorize('projects-update');

            $formProject = Project::with('members')->findOrFail($request->integer('edit'));
        }
        $clients = Client::whereNull('deleted_at')
            ->orderBy('name')
            ->get();
        $users = User::whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view(
            'projects.index',
            compact('projects', 'status', 'counts', 'formProject', 'clients', 'users')
        );
    }

    public function create()
    {
        $this->authorize('projects-create');

        return view('projects.create', [
            'project' => new Project(),
            'clients' => Client::whereNull('deleted_at')
                ->orderBy('name')
                ->get(),
            'users' => User::whereNull('deleted_at')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('projects-create');

        $data = $this->data($request);

        DB::transaction(function () use (&$project, $data) {
            $members = $data['member_ids'] ?? [];

            unset($data['member_ids']);

            $data['created_by'] = Auth::id();

            $project = Project::create($data);

            $project->members()->sync(
                array_unique(
                    array_filter(
                        array_merge(
                            $members,
                            [$project->project_manager_id]
                        )
                    )
                )
            );
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Request $request, Project $project)
    {
        $this->authorize('projects-show', $project);


        $project->load([
            'client',
            'manager',
            'members',
            'creator',
        ]);

        $storyStatuses = $project->stories()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $taskStatuses = $project->tasks()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $trackedSeconds = (int) DB::table('tracker_histories')
            ->join('tasks', 'tracker_histories.task_id', '=', 'tasks.id')
            ->where('tasks.project_id', $project->id)
            ->sum('tracker_histories.elapsed_time');

        $overview = [
            'stories' => $storyStatuses,
            'tasks' => $taskStatuses,
            'story_total' => $storyStatuses->sum(),
            'task_total' => $taskStatuses->sum(),
            'tracked_seconds' => $trackedSeconds,
            'estimated_hours' => (float) $project->tasks()->sum('estimated_hours'),
        ];

        $showEstimatedHours = Auth::user()->hasRole(\App\Support\Roles::CLIENT);

        $formStory = new Story(['project_id' => $project->id]);

        if ($request->filled('edit_story')) {
            $this->authorize('projects-stories-update');

            $formStory = Story::findOrFail($request->integer('edit_story'));
            abort_unless($formStory->project_id === $project->id, 404);
        }

        $stories = null;

        if ($request->get('tab') === 'stories') {
            $stories = $project->stories()
                ->select('stories.*')
                ->with('creator:id,name')
                ->withCount('tasks')
                ->selectSub(function ($query) {
                    $query->from('tasks')
                        ->selectRaw('COALESCE(SUM(estimated_hours), 0)')
                        ->whereColumn('tasks.story_id', 'stories.id');
                }, 'estimated_hours_total')
                ->selectSub(function ($query) {
                    $query->from('tracker_histories')
                        ->join('tasks', 'tracker_histories.task_id', '=', 'tasks.id')
                        ->selectRaw('COALESCE(SUM(tracker_histories.elapsed_time), 0)')
                        ->whereColumn('tasks.story_id', 'stories.id');
                }, 'tracked_seconds_total')
                ->latest()
                ->paginate(12)
                ->withQueryString();
        }

        $currentFolder = null;
        $folders = collect();
        $projectFiles = collect();
        if ($request->get('tab') === 'files') {
            $currentFolder = $request->filled('folder') ? ProjectFolder::where('project_id', $project->id)->findOrFail($request->integer('folder')) : null;
            $folders = ProjectFolder::with('creator')->where('project_id', $project->id)->where('parent_id', $currentFolder?->id)->orderBy('name')->get();
            $projectFiles = ProjectFile::with('uploader')->where('project_id', $project->id)->where('folder_id', $currentFolder?->id)->latest()->get();
        }

        return view(
            'projects.show',
            compact('project', 'overview', 'formStory', 'stories', 'currentFolder', 'folders', 'projectFiles', 'showEstimatedHours')
        );
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('projects-update');

        $data = $this->data($request);

        DB::transaction(function () use ($project, $data) {
            $members = $data['member_ids'] ?? [];

            unset($data['member_ids']);

            $project->update($data);

            $project->members()->sync(
                array_unique(
                    array_filter(
                        array_merge(
                            $members,
                            [$project->project_manager_id]
                        )
                    )
                )
            );
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('projects-delete');

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
