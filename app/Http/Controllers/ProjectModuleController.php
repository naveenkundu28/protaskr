<?php
namespace App\Http\Controllers;
use App\Models\{Client, Project, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class ProjectModuleController extends Controller
{
    private function canView(Project $project): void
    {
        abort_unless(
            Auth::user()->hasRole('Admin')
            || $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->members()->whereKey(Auth::id())->exists(),
            403
        );
    }

    private function data(Request $request): array
    {
        return $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'project_type' => 'nullable|string|max:100',
            'priority' => 'required|in:low,medium,high',
            'budget_hours' => 'nullable|numeric|min:0',
            'project_manager_id' => 'nullable|exists:users,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
            'status' => 'required|in:active,completed,on_hold,not_started',
        ]);
    }

    public function index(Request $request)
    {
        $this->authorize('project-show');

        $status = $request->get('status', 'all');

        $projects = Project::with(['client', 'manager'])
            ->withCount('tasks')
            ->when(
                $status !== 'all',
                fn ($q) => $q->where('status', $status)
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
        )->first();

        $project = new Project();
        $clients = Client::whereNull('deleted_at')
            ->orderBy('name')
            ->get();
        $users = User::whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view(
            'projects.index',
            compact('projects', 'status', 'counts', 'project', 'clients', 'users')
        );
    }

    public function create()
    {
        $this->authorize('project-create');

        return view('projects.form', [
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
        $this->authorize('project-create');

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

    public function show(Project $project)
    {
        $this->authorize('project-show');

        $this->canView($project);

        $project->load([
            'client',
            'manager',
            'members',
        ]);

        return view(
            'projects.show',
            compact('project')
        );
    }

    public function edit(Project $project)
    {
        $this->authorize('project-edit');

        $this->canView($project);

        $project->load('members');

        return view('projects.form', [
            'project' => $project,
            'clients' => Client::whereNull('deleted_at')
                ->orderBy('name')
                ->get(),
            'users' => User::whereNull('deleted_at')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('project-edit');

        $this->canView($project);

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

    public function stories(Request $request, Project $project)
    {
        $this->authorize('project-show');

        $this->canView($project);

        return redirect()->route(
            'projects.show',
            [
                'project' => $project,
                'tab' => 'stories',
            ] + $request->only('search')
        );
    }

    public function tasks(Request $request, Project $project)
    {
        $this->authorize('task-show');

        $this->canView($project);

        return redirect()->route(
            'projects.show',
            [
                'project' => $project,
                'tab' => 'tasks',
            ] + $request->only(
                'search',
                'status'
            )
        );
    }
}