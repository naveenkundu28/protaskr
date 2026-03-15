<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\Project;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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

        $projects = $projects->with(['users' => function ($query) {
            $query->withTrashed();
        }])->latest()
            ->paginate(50)
            ->withQueryString();
        // dd($projects);

        return view('clients.index', compact('projects'));
    }

    public function create()
    {
        $this->authorize('project-create');

        $employees = User::role('Employee')->withTrashed()->get();
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
        $teamMembers = $client->allUsers()->get();
        $users = User::withTrashed()->get();
        return view('clients.show', compact('client', 'teamMembers', 'users'));
    }
    public function edit(Client $client)
    {
        $this->authorize('project-edit');
        $employees = User::role('Employee')->withTrashed()->get();
        $client->load([
            'teamProjects' => function ($query) {
                $query->withTrashed();
            }
        ]);
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

        // DB::table('client_teams')->where('client_id', $id)->delete();

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

        $services = $client->services()
            ->with(['frontResource', 'workingResource'])
            ->select('*')
            ->selectSub(function ($query) {
                $query->from('services as s2')
                    ->select('start_date')
                    ->whereColumn('s2.service_id', 'services.service_id')
                    ->where('service_status', 'new')
                    ->limit(1);
            }, 'main_start_date')
            ->latest()
            ->paginate(10);

        // $services = $client->services()
        //     ->with(['frontResource', 'workingResource'])
        //     ->latest()
        //     ->paginate(10);

        // return print_r($services);

        $employees = $client->users()->role('Employee')->withTrashed()->get();

        return view('clients.services.index', compact('services', 'client', 'employees'));
    }

    public function serviceListing()
    {
        $this->authorize('service-show');

        $services = Service::with(['client', 'frontResource', 'workingResource'])
            ->select('*')
            ->selectSub(function ($query) {
                $query->from('services as s2')
                    ->select('start_date')
                    ->whereColumn('s2.service_id', 'services.service_id')
                    ->where('service_status', 'new')
                    ->limit(1);
            }, 'main_start_date')
            ->latest()
            ->paginate(20);

        return view('clients.services.listing', compact('services'));
    }



    public function storeService(Request $request, Client $client)
    {
        $this->authorize('service-create');

        $request->validate([
            'service_type' => 'required|in:direct,proxy,external',
            'service_id' => 'required',
            // 'month' => 'required|numeric',
            'hours' => 'required|numeric',
            'service_status' => 'required|in:new,in_progress,completed,replacement,canceled',

            'front_resource' => 'nullable|exists:users,id',
            'working_resource' => 'nullable|exists:users,id',

            'start_date' => [
                'nullable',
                'date',
                Rule::requiredIf($request->service_status === 'new')
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
                Rule::requiredIf($request->service_status === 'completed' || $request->service_status === 'canceled')
            ],

            'cancel_reason' => [
                'nullable',
                'string',
                Rule::requiredIf($request->service_status === 'canceled')
            ],
            'replacement_reason' => [
                'nullable',
                'string',
                Rule::requiredIf($request->service_status === 'replacement')
            ],
        ]);

        $data = $request->only([
            'service_type',
            'service_id',
            // 'month',
            'hours',
            'service_status',
            'front_resource',
            'working_resource',
            'start_date',
            'end_date',
            'cancel_reason',
            'replacement_reason',
        ]);


        // START: check if same month(created_at), same service_id & same front & working resource then record cant be inserted or updated, it can be inserted if different month
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $exists = $client->services()
            ->where('service_id', $request->service_id)
            ->where('front_resource', $request->front_resource)
            ->where('working_resource', $request->working_resource)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->exists();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'service_id' => 'This service already exists for the same resources in the current month.'
                ]);
        }

        // END: check if same month(created_at), same service_id & same front & working resource then record cant be inserted or updated, it can be inserted if different month

        // clean fields depending on status
        if ($data['service_status'] !== 'new') {
            $data['start_date'] = null;
        }

        if ($data['service_status'] !== 'completed' || $data['service_status'] !== 'canceled' || $data['service_status'] !== 'replacement') {
            $data['end_date'] = null;
        }

        if ($data['service_status'] !== 'canceled') {
            $data['cancel_reason'] = null;
        }

        if ($data['service_status'] !== 'replacement') {
            $data['replacement_reason'] = null;
        }

        $client->services()->create($data);

        return redirect()
            ->route('clients.services.index', $client->id)
            ->with('success', 'Service created successfully');
    }

    public function servicesShow(Client $client, Service $service)
    {
        $this->authorize('service-show');
        $service->load(['frontResource', 'workingResource']);
        return view('clients.services.show', compact('client', 'service'));
    }

    public function updateService(Request $request, Client $client, Service $service)
    {
        $this->authorize('service-edit');



        $rules = [
            'service_type'      => 'required|in:direct,proxy,external',
            'service_id'        => 'required',
            // 'month'             => 'required|numeric',
            'hours'             => 'required|numeric',
            'service_status'    => 'required|in:new,in_progress,completed,replacement,canceled',
            'front_resource'    => 'required|exists:users,id',
            'working_resource'  => 'required|exists:users,id',
            'start_date' => [
                'nullable',
                'date',
                Rule::requiredIf($request->service_status === 'new')
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
                Rule::requiredIf($request->service_status === 'completed' || $request->service_status === 'canceled')
            ],

            'cancel_reason' => [
                'nullable',
                'string',
                Rule::requiredIf($request->service_status === 'canceled')
            ],
            'replacement_reason' => [
                'nullable',
                'string',
                Rule::requiredIf($request->service_status === 'replacement')
            ],
        ];



        $request->validate($rules);

        $data = $request->only([
            'service_type',
            'service_id',
            // 'month',
            'hours',
            'service_status',
            'front_resource',
            'working_resource',
            'start_date',
            'end_date',
            'cancel_reason',
            'replacement_reason'
        ]);


        // START: check if same month(created_at), same service_id & same front & working resource then record cant be inserted or updated, it can be inserted if different month
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $exists = $client->services()
            ->where('service_id', $request->service_id)
            ->where('id', '!=', $service->id)
            ->where('front_resource', $request->front_resource)
            ->where('working_resource', $request->working_resource)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->exists();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'service_id' => 'This service already exists for the same resources in the current month.'
                ]);
        }

        // END: check if same month(created_at), same service_id & same front & working resource then record cant be inserted or updated, it can be inserted if different month



        // clean fields depending on status
        if ($data['service_status'] !== 'new') {
            $data['start_date'] = null;
        }

        if ($data['service_status'] !== 'completed' && $data['service_status'] !== 'canceled' && $data['service_status'] !== 'replacement') {
            $data['end_date'] = null;
        }

        if ($data['service_status'] !== 'canceled') {
            $data['cancel_reason'] = null;
        }
        if ($data['service_status'] !== 'replacement') {
            $data['replacement_reason'] = null;
        }

        $service->update($data);

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
