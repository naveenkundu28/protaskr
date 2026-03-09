<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\Project;
use App\Models\Service;
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

        $projects = $projects->with('users')->latest()
            ->paginate(50)
            ->withQueryString();
        // dd($projects);

        return view('clients.index', compact('projects'));
    }

    public function create()
    {
        $this->authorize('project-create');

        $employees = User::role('Employee')->get();
        return view('clients.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $this->authorize('project-create');

        $request->validate([
            'name' => 'required|string|max:255',
            'client_email' => 'required|email',
            'client_code' => 'required',
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'required|in:not_started,in_progress,completed',
            'budget' => 'nullable|numeric',
            'employee' => 'required|array',
        ]);

        $project = Auth::user()->projects()->create($request->all());
        $project->teamProjects()->attach($request->employee);
        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }


    public function show(Client $client)
    {
        $this->authorize('project-show');
        $teamMembers = $client->users()->get();
        $users = User::all();
        return view('clients.show', compact('client', 'teamMembers', 'users'));
    }
    public function edit(Client $client)
    {
        $this->authorize('project-edit');
        $employees = User::role('Employee')->get();
        $client->load('teamProjects'); // eager load relation

        return view('clients.edit', compact('client', 'employees'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('project-edit');

        $request->validate([
            'name' => 'required|string|max:255',
            'client_email' => 'required|email',
            'client_code' => 'required',
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'required|in:not_started,in_progress,completed',
            'budget' => 'nullable|numeric',
            'employee' => 'required|array',
        ]);

        $client->update($request->all());

        // Sync employees
        $client->teamProjects()->sync($request->employee);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy($id)
    {
        // dd($client);
        $this->authorize('project-delete');

        DB::table('client_teams')->where('client_id', $id)->delete();

        Client::findOrFail($id)->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }



    public function addMember(Request $request)
    {
        $this->authorize('project-edit');

        $request->validate([
            'project_id' => 'required|exists:clients,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $project = Client::find($request->project_id);
        $project->teamProjects()->attach($request->user_id);
        return redirect()->back()->with('success', 'User added successfully.');
    }
    public function removeMember(Request $request)
    {
        $this->authorize('project-edit');

        $request->validate([
            'project_id' => 'required|exists:clients,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $project = Client::find($request->project_id);
        $project->teamProjects()->detach($request->user_id);

        return redirect()->back()->with('success', 'User removed successfully.');
    }
    public function getProjectUsers($clientId)
    {
        $client = Client::find($clientId);

        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        $users = $client->sortedUsers();
        return response()->json($users);
    }
    public function restore($clientId)
    {
        $record = Client::withTrashed()->findOrFail($clientId);
        $record->restore();

        return redirect()->back()->with('success', 'Client restored successfully.');
    }


    public function services(Client $client)
    {
        $this->authorize('service-show');

        $services = $client->services()->with('users')->paginate(10);

        return view('clients.services.index', compact('services', 'client'));
    }

    public function createService(Client $client)
    {
        $this->authorize('service-create');

        $employees = $client->users()->role('Employee')->get();

        return view('clients.services.create', compact('client', 'employees'));
    }

    public function storeService(Request $request, Client $client)
    {
        $this->authorize('service-create');

        $request->validate([
            'service_type' => 'required|in:direct,proxy,external',
            'service_id' => 'required|unique:services',
            'hours' => 'nullable|numeric',
            'service_status' => 'required|in:new,continuing,closed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'employee' => 'required|array'
        ]);

        $service = $client->services()->create($request->all());

        $service->users()->attach($request->employee);

        return redirect()
            ->route('clients.services.index', $client->id)
            ->with('success', 'Service created successfully');
    }

    public function editService(Client $client, Service $service)
    {
        $this->authorize('service-edit');

        $service->load('users');
        $employees = $client->users()->role('Employee')->get();

        return view('clients.services.edit', compact('service', 'employees', 'client'));
    }

    public function updateService(Request $request, Client $client, Service $service)
    {
        $this->authorize('service-edit');

        $request->validate([
            'service_type' => 'required|in:direct,proxy,external',
            'service_id' => 'required|unique:services,service_id,' . $service->id,
            'hours' => 'nullable|numeric',
            'service_status' => 'required|in:new,continuing,closed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'employee' => 'required|array'
        ]);

        $service->update($request->all());

        $service->users()->sync($request->employee);

        return redirect()
            ->route('clients.services.index', $client->id)
            ->with('success', 'Service updated successfully');
    }

    public function destroyService(Client $client, Service $service)
    {
        $this->authorize('service-delete');

        $service->delete();

        return back()->with('success', 'Service deleted successfully');
    }
}
