<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Hour;
use App\Models\User;
use App\Models\Project;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('project-show');
        $user = Auth::user();
        $search = trim((string) $request->input('search'));

        $projectQuery = $user->hasRole('Admin') ? $user->projects() : $user->projectMembers();

        $projects = $projectQuery->when($request->type == 'archived', function ($query) {
            return $query->onlyTrashed();
        })->when($search !== '', function ($query) use ($search) {
            return $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('client_email', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('start_date', 'like', "%{$search}%")
                    ->orWhere('end_date', 'like', "%{$search}%")
                    ->orWhere('budget', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('client_code', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhereHas('users', function ($memberQuery) use ($search) {
                        $memberQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });
            });
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
        $users = User::withTrashed()->role('Employee')->get();
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
        $search = trim((string) request('search'));
        $tab = request('tab', 'active');
        $activeStatuses = ['new', 'in_progress'];
        $closedStatuses = ['completed', 'replacement', 'canceled'];
        $activeTab = in_array($tab, ['active', 'closed']) ? $tab : 'active';
        $statusFilter = $activeTab === 'closed' ? $closedStatuses : $activeStatuses;

        $services = $client->services()
            ->with(['frontResource', 'workingResource'])
            ->whereIn('service_status', $statusFilter)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('service_id', 'like', "%{$search}%")
                        ->orWhere('service_type', 'like', "%{$search}%")
                        ->orWhere('service_status', 'like', "%{$search}%")
                        ->orWhere('hours', 'like', "%{$search}%")
                        ->orWhereHas('frontResource', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('employee_id', 'like', "%{$search}%");
                        })
                        ->orWhereHas('workingResource', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('employee_id', 'like', "%{$search}%");
                        });
                });
            })
            ->select('*')
            ->selectSub(function ($query) {
                $query->from('services as s2')
                    ->select('start_date')
                    ->whereColumn('s2.service_id', 'services.service_id')
                    ->where('service_status', 'new')
                    ->limit(1);
            }, 'main_start_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $employees = $client->users()->role('Employee')->get();

        // Fetch all master hours
        $masterHours = Hour::all()->keyBy(function ($item) {
            return $item->year . '-' . $item->month;
        });

        return view('clients.services.index', compact('services', 'client', 'employees', 'masterHours', 'activeTab'));
    }

    public function serviceListing()
    {
        $this->authorize('service-show');
        $search = trim((string) request('search'));
        $clientId = request('client_id');
        $month = request('month');
        $frontResource = request('front_resource');

        $services = Service::with(['client', 'frontResource', 'workingResource'])
            ->when(!empty($clientId), function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })
            ->when(!empty($month), function ($query) use ($month) {
                $query->whereMonth('created_at', $month);
            })
            ->when(!empty($frontResource), function ($query) use ($frontResource) {
                $query->where('front_resource', $frontResource);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('service_id', 'like', "%{$search}%")
                        ->orWhere('service_type', 'like', "%{$search}%")
                        ->orWhere('service_status', 'like', "%{$search}%")
                        ->orWhere('hours', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($clientQuery) use ($search) {
                            $clientQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('client_id', 'like', "%{$search}%");
                        })
                        ->orWhereHas('frontResource', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('employee_id', 'like', "%{$search}%");
                        })
                        ->orWhereHas('workingResource', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('employee_id', 'like', "%{$search}%");
                        });
                });
            })
            ->select('*')
            ->selectSub(function ($query) {
                $query->from('services as s2')
                    ->select('start_date')
                    ->whereColumn('s2.service_id', 'services.service_id')
                    ->where('service_status', 'new')
                    ->limit(1);
            }, 'main_start_date')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Fetch all master hours
        $masterHours = Hour::all()->keyBy(function ($item) {
            return $item->year . '-' . $item->month;
        });

        $clients = Client::whereHas('services')
            ->orderBy('name')
            ->get(['id', 'name']);

        $frontResources = User::whereIn('id', Service::whereNotNull('front_resource')->distinct()->pluck('front_resource'))
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        $monthOptions = collect(range(1, 12))->map(function ($monthNumber) {
            return [
                'value' => $monthNumber,
                'label' => Carbon::create()->month($monthNumber)->format('F'),
            ];
        });

        return view('clients.services.listing', compact(
            'services',
            'masterHours',
            'clients',
            'frontResources',
            'monthOptions'
        ));
    }

    public function importServices(Request $request)
    {
        $this->authorize('service-create');

        $request->validate([
            'services_file' => 'required|file|mimes:xls,xlsx',
        ]);

        $rows = Excel::toArray([], $request->file('services_file'));

        $sheetRows = $rows[0] ?? [];

        if (count($sheetRows) < 2) {
            return redirect()
                ->route('services.listing')
                ->withErrors(['services_file' => 'The uploaded sheet does not contain any service rows.'])
                ->withInput();
        }

        $headers = collect(array_shift($sheetRows))
            ->map(fn($header) => $this->normalizeImportHeader($header))
            ->all();

        $errors = [];
        $servicesToCreate = [];
        $clientsToCreate = [];

        foreach ($sheetRows as $index => $row) {
            if (collect($row)->filter(fn($value) => $value !== null && $value !== '')->isEmpty()) {
                continue;
            }

            $rowNumber = $index + 2;
            $rowErrors = [];
            $data = [];

            foreach ($headers as $columnIndex => $header) {
                if ($header === '') {
                    continue;
                }

                $data[$header] = $row[$columnIndex] ?? null;
            }

            $clientCode = trim((string) ($data['client_id'] ?? $data['client_code'] ?? ''));
            $clientName = trim((string) ($data['client_name'] ?? $data['name'] ?? ''));
            $serviceId = trim((string) ($data['service_id'] ?? ''));

            if ($clientCode === '' || $clientName === '' || $serviceId === '') {
                $rowErrors[] = "Row {$rowNumber}: client name, client id, and service id are required.";
                continue;
            }

            $frontEmployeeId = trim((string) ($data['front_resource_id'] ?? $data['front_end_resource_id'] ?? ''));
            $workingEmployeeId = trim((string) ($data['back_end_resource_id'] ?? $data['working_resource_id'] ?? $data['backend_resource_id'] ?? ''));

            $frontUser = $frontEmployeeId !== '' ? User::withTrashed()->where('employee_id', $frontEmployeeId)->first() : null;
            $workingUser = $workingEmployeeId !== '' ? User::withTrashed()->where('employee_id', $workingEmployeeId)->first() : null;

            if ($frontEmployeeId !== '' && !$frontUser) {
                $rowErrors[] = "Row {$rowNumber}: employee id {$frontEmployeeId} is not present in the system.";
            }

            if ($workingEmployeeId !== '' && !$workingUser) {
                $rowErrors[] = "Row {$rowNumber}: employee id {$workingEmployeeId} is not present in the system.";
            }

            if (!empty($rowErrors)) {
                $errors = array_merge($errors, $rowErrors);
                continue;
            }

            $clientsToCreate[$clientCode] = [
                'name' => $clientName,
                'client_code' => $clientCode,
                'project_name' => trim((string) ($data['project_name'] ?? $clientName)),
                'client_email' => trim((string) ($data['client_email'] ?? "import-{$clientCode}@example.com")),
                'description' => trim((string) ($data['description'] ?? 'Imported from service listing')),
                'status' => trim((string) ($data['status'] ?? 'in_progress')),
                'start_date' => $this->normalizeImportDate($data['client_start_date'] ?? $data['start_date'] ?? null),
                'end_date' => $this->normalizeImportDate($data['client_end_date'] ?? null),
                'budget' => is_numeric($data['budget'] ?? null) ? $data['budget'] : null,
            ];

            $servicesToCreate[] = [
                'row_number' => $rowNumber,
                'client_code' => $clientCode,
                'service_type' => strtolower(trim((string) ($data['type'] ?? $data['service_type'] ?? 'direct'))),
                'service_id' => $serviceId,
                'hours' => is_numeric($data['hours'] ?? null) ? $data['hours'] : 0,
                'service_status' => strtolower(trim((string) ($data['service_status'] ?? $data['status'] ?? 'new'))),
                'front_resource' => $frontUser?->id,
                'working_resource' => $workingUser?->id,
                'start_date' => $this->normalizeImportDate($data['service_start_date'] ?? $data['start_date'] ?? null),
                'end_date' => $this->normalizeImportDate($data['end_date'] ?? null),
                'cancel_reason' => $data['cancel_reason'] ?? null,
                'replacement_reason' => $data['replacement_reason'] ?? null,
            ];
        }

        if (!empty($errors)) {
            return redirect()
                ->route('services.listing')
                ->withErrors(['services_file' => implode("\n", $errors)]);
        }

        DB::transaction(function () use ($clientsToCreate, $servicesToCreate) {
            $clientIds = [];

            foreach ($clientsToCreate as $clientCode => $clientData) {
                $client = Client::withTrashed()->firstWhere('client_code', $clientCode);

                if ($client && $client->trashed()) {
                    $client->restore();
                }

                if (!$client) {
                    $client = Client::create([
                        'user_id' => Auth::id(),
                        'name' => $clientData['name'],
                        'client_code' => $clientData['client_code'],
                        'project_name' => $clientData['project_name'],
                        'client_email' => $clientData['client_email'],
                        'description' => $clientData['description'],
                        'status' => in_array($clientData['status'], ['not_started', 'in_progress', 'completed']) ? $clientData['status'] : 'in_progress',
                        'start_date' => $clientData['start_date'],
                        'end_date' => $clientData['end_date'],
                        'budget' => $clientData['budget'],
                    ]);
                }

                $clientIds[$clientCode] = $client->id;
            }

            foreach ($servicesToCreate as $serviceData) {
                $clientId = $clientIds[$serviceData['client_code']];

                Service::create([
                    'client_id' => $clientId,
                    'service_type' => in_array($serviceData['service_type'], ['direct', 'proxy', 'external']) ? $serviceData['service_type'] : 'direct',
                    'service_id' => $serviceData['service_id'],
                    'hours' => $serviceData['hours'],
                    'service_status' => in_array($serviceData['service_status'], ['new', 'in_progress', 'completed', 'replacement', 'canceled']) ? $serviceData['service_status'] : 'new',
                    'front_resource' => $serviceData['front_resource'],
                    'working_resource' => $serviceData['working_resource'],
                    'start_date' => $serviceData['start_date'],
                    'end_date' => $serviceData['end_date'],
                    'cancel_reason' => $serviceData['cancel_reason'],
                    'replacement_reason' => $serviceData['replacement_reason'],
                ]);

                $memberIds = collect([$serviceData['front_resource'], $serviceData['working_resource']])
                    ->filter()
                    ->unique()
                    ->all();

                if (!empty($memberIds)) {
                    Client::find($clientId)?->teamProjects()->syncWithoutDetaching($memberIds);
                }
            }
        });

        return redirect()
            ->route('services.listing')
            ->with('success', 'Services imported successfully.');
    }

    private function normalizeImportHeader($header): string
    {
        return Str::of((string) $header)
            ->lower()
            ->replace(['-', ' '], '_')
            ->replace('/', '_')
            ->replace('__', '_')
            ->trim('_')
            ->toString();
    }

    private function normalizeImportDate($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->toDateString();
            }

            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
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
        if ($serviceValidationError = $this->validateMonthlyServiceHours($client, $request)) {
            return $serviceValidationError;
        }

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $sameServiceExists = $client->services()
            ->where('service_id', $request->service_id)
            ->where('working_resource', $request->working_resource)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->exists();

        if ($sameServiceExists) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'service_err' => 'This service already exists for this resource in the current month.'
                ]);
        }

        // /*
        // |--------------------------------------------------------------------------
        // | Rule 2: Same service cannot repeat in same month/year
        // |--------------------------------------------------------------------------
        // */
        // $resourceExists = $client->services()
        //     ->where('service_id', $request->service_id)
        //     // ->where('front_resource', $request->front_resource)
        //     ->whereMonth('created_at', $currentMonth)
        //     ->whereYear('created_at', $currentYear)
        //     ->exists();

        // if ($resourceExists) {
        //     return redirect()
        //         ->back()
        //         ->withInput()
        //         ->withErrors([
        //             'service_err' => 'This service is already assigned to somebody for this month.'
        //         ]);
        // }


        /*
        |--------------------------------------------------------------------------
        | Rule 3: Same direct front resource cannot repeat in same month/year
        |--------------------------------------------------------------------------
        */

        if ($request->service_type === 'direct') {
            $directResourceExists = $client->services()
                ->where('service_id', $request->service_id)
                ->where('service_type', 'direct')
                ->where('front_resource', $request->front_resource)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->exists();

            if ($directResourceExists) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'service_err' => 'This direct resource is already assigned to some service for this month.'
                    ]);
            }
        }

        // END: check if same month(created_at), same service_id & same front resource then record cant be inserted or updated, it can be inserted if different month

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
        if ($serviceValidationError = $this->validateMonthlyServiceHours($client, $request, $service)) {
            return $serviceValidationError;
        }

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $sameServiceExists = $client->services()
            ->where('service_id', $request->service_id)
            ->where('id', '!=', $service->id)
            ->where('working_resource', $request->working_resource)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->exists();

        if ($sameServiceExists) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'service_err' => 'This service already exists for the same resources in the current month.'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Rule 2: Same service cannot repeat in same month/year
        |--------------------------------------------------------------------------
        */
        // $resourceExists = $client->services()
        //     ->where('service_id', $request->service_id)
        //     ->where('id', '!=', $service->id)
        //     // ->where('front_resource', $request->front_resource)
        //     ->whereMonth('created_at', $currentMonth)
        //     ->whereYear('created_at', $currentYear)
        //     ->exists();

        // if ($resourceExists) {
        //     return redirect()
        //         ->back()
        //         ->withInput()
        //         ->withErrors([
        //             'service_err' => 'This service is already assigned to somebody for this month.'
        //         ]);
        // }


        /*
        |--------------------------------------------------------------------------
        | Rule 3: Same direct front resource cannot repeat in same month/year
        |--------------------------------------------------------------------------
        */

        if ($request->service_type === 'direct') {
            $directResourceExists = $client->services()
                ->where('service_type', 'direct')
                ->where('id', '!=', $service->id)

                ->where('front_resource', $request->front_resource)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->exists();

            if ($directResourceExists) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'service_err' => 'This direct resource is already assigned to some service for this month.'
                    ]);
            }
        }

        // END: check if same month(created_at), same service_id & same front resource then record cant be inserted or updated, it can be inserted if different month



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

    private function validateMonthlyServiceHours(Client $client, Request $request, ?Service $service = null)
    {
        $currentDate = Carbon::now();
        $currentMonth = $currentDate->month;
        $currentMonthName = $currentDate->format('F');
        $currentYear = $currentDate->year;

        $maxHours = Hour::where('year', $currentYear)
            ->where('month', $currentMonthName)
            ->value('hours');

        if (!$maxHours) {
            return back()->withInput()->withErrors([
                'service_err' => "Master hours are not set for {$currentMonthName} {$currentYear}.",
            ]);
        }

        $serviceHoursQuery = $client->services()
            ->where('service_id', $request->service_id)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear);

        if ($service) {
            $serviceHoursQuery->where('id', '!=', $service->id);
        }

        $usedHoursForServiceId = (float) $serviceHoursQuery->sum('hours');
        $remainingHour = (float) $maxHours - $usedHoursForServiceId;

        if ($usedHoursForServiceId >= (float) $maxHours) {
            return back()->withInput()->withErrors([
                'hours' => "Hours for service ID {$request->service_id} have already reached the monthly limit of {$maxHours}.",
            ]);
        }

        if ((float) $request->hours > $remainingHour) {
            return back()->withInput()->withErrors([
                'hours' => "Only {$remainingHour} hours are available for service ID {$request->service_id} this month.",
            ]);
        }

        $sameServiceIdExists = (clone $serviceHoursQuery)->exists();

        if ($sameServiceIdExists && $request->service_type === 'direct') {
            return back()->withInput()->withErrors([
                'service_type' => 'For an existing service ID in the current month, service type cannot be direct.',
            ]);
        }

        return null;
    }
}
