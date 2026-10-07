<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DailyPerformance;
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

        $typeFilter = $request->input('type_filter', []);
        $search = trim((string) $request->input('search'));

        $projectQuery = $user->hasRole('Admin') ? $user->projects() : $user->projectMembers();

        $projects = $projectQuery->when($request->type == 'archived', function ($query) {
            return $query->onlyTrashed();
        }, function ($query) use ($request) {

            // Non-archived → exclude deleted
            $query->whereNull('deleted_at');

            // Completed tab
            if ($request->type == 'completed') {
                $query->where('status', 'completed');
            } else {
                $query->where(function ($q) {
                    $q->where('status', 'in_progress')
                        ->orWhere('status', 'not_started');
                });
            }
        })->when($search !== '', function ($query) use ($search) {
            return $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('client_email', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('start_date', 'like', "%{$search}%")
                    ->orWhere('end_date', 'like', "%{$search}%")
                    ->orWhere('budget', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    // ->orWhere('client_code', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhereHas('users', function ($memberQuery) use ($search) {
                        $memberQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });
            });
        })
            ->when(!empty($typeFilter), function ($query) use ($typeFilter) {
                $query->whereIn('type', $typeFilter);
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
        }])
            ->withSum([
                'services as current_month_hours' => function ($query) {
                    $query->where('month', now()->month)
                        ->where('year', now()->year);
                }
            ], 'hours')
            ->latest()
            ->paginate(50)
            ->withQueryString();
        // dd($projects);

        $performances = DailyPerformance::all();

        return view('clients.index', compact('projects', 'performances'));
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
            // 'client_code' => 'required',
            'project_name' => 'required|string|max:255',
            'type' => 'required|in:internal_team,external_team,client,project',
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
        $this->authorizeClientAccess($client);

        $teamMembers = $client->allUsers()->get();
        $users = User::withTrashed()->role('Employee')->get();
        return view('clients.show', compact('client', 'teamMembers', 'users'));
    }
    public function edit(Client $client)
    {
        // $this->authorize('project-edit');
        if (!auth()->user()->can('project-edit') && !auth()->user()->hasRole('Employee')) {
            abort(403);
        }
        $employees = User::role('Employee')->withTrashed()->get();
        $client->load([
            'teamProjects' => function ($query) {
                $query->withTrashed();
            },
            'emailUpdatedBy' => function ($query) {
                $query->withTrashed();
            }
        ]);
        return view('clients.edit', compact('client', 'employees'));
    }

    public function update(Request $request, Client $client)
    {
        // $this->authorize('project-edit');
        if (!auth()->user()->can('project-edit') && !auth()->user()->hasRole('Employee')) {
            abort(403);
        }

        if (auth()->user()->can('project-edit')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'client_email' => 'required|email',
                // 'client_code' => 'required',
                'project_name' => 'required|string|max:255',
                'type' => 'required|in:internal_team,external_team,client,project',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'status' => 'required|in:not_started,in_progress,completed',
                'budget' => 'nullable|numeric',
                'employee' => 'required|array',
            ]);
        } else {
            $request->validate([
                'client_email' => 'required|email',
            ]);
        }


        // $client->update($request->all());

        // // Sync employees
        // $client->teamProjects()->sync($request->employee);

        $emailChanged = $client->client_email !== $request->client_email;
        if (auth()->user()->can('project-edit')) {
            // Admin → update all fields

            $data = $request->all();

            if ($emailChanged) {
                $data['email_updated_by'] = auth()->user()->id;
                $data['email_updated_at'] = now();
            }
            $client->update($data);

            $client->teamProjects()->sync($request->employee);
        } else {
            // Employee → update ONLY email
            $client->update([
                'client_email' => $request->client_email,
                'email_updated_by' => auth()->user()->id,
                'email_updated_at' => now()
            ]);
        }

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

    public function markCompleted(Request $request, Client $client)
    {
        if (!auth()->user()->can('project-edit') && !auth()->user()->hasRole('Employee')) {
            abort(403);
        }

        $validated = $request->validate([
            'end_date' => 'required|date',
        ]);

        if (!empty($client->start_date) && Carbon::parse($validated['end_date'])->lt(Carbon::parse($client->start_date))) {
            return back()->withErrors([
                'end_date' => 'End date must be after or equal to start date.',
            ])->withInput();
        }

        $client->update([
            'status' => 'completed',
            'end_date' => $validated['end_date'],
        ]);

        return redirect()->route('clients.index')->with('success', 'Client marked as completed successfully.');
    }


    public function services(Client $client)
    {
        $this->authorize('service-show');
        $this->authorizeClientAccess($client);

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

        $tab = request('tab', 'active');
        $activeStatuses = ['new', 'in_progress'];
        $closedStatuses = ['completed', 'replacement', 'canceled'];
        $activeTab = in_array($tab, ['active', 'closed']) ? $tab : 'active';
        $statusFilter = $activeTab === 'closed' ? $closedStatuses : $activeStatuses;

        $clientId = request('client_id');
        $month = request('month');
        $frontResource = request('front_resource');
        $workResource = request('work_resource');

        $services = Service::with(['client', 'frontResource', 'workingResource'])
            ->whereIn('service_status', $statusFilter)
            ->when(!empty($clientId), function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })
            ->when(!empty($month), function ($query) use ($month) {
                $query->whereMonth('created_at', $month);
            })
            ->when(!empty($frontResource), function ($query) use ($frontResource) {
                $query->where('front_resource', $frontResource);
            })
            // ->when(!empty($workResource), function ($query) use ($workResource) {
            //     $query->where('front_resource', $workResource);
            // })
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

        // $workResources = User::whereIn('id', Service::whereNotNull('working_resource')->distinct()->pluck('working_resource'))
        //     ->orderBy('name')
        //     ->get(['id', 'name', 'employee_id']);

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
            // 'workResources',
            'monthOptions',
            'activeTab'
        ));
    }

    public function importServices(Request $request)
    {
        $this->authorize('service-create');

        $request->validate([
            'services_file' => 'required|file|mimes:xls,xlsx,csv,txt',
        ]);

        $rows = Excel::toArray([], $request->file('services_file'));

        $sheetRows = $rows[0] ?? [];

        if (count($sheetRows) < 1) {
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
        $seenDirectServices = [];
        $serviceHoursTracker = [];

        foreach ($sheetRows as $index => $row) {
            $rowNumber = $index + 2;

            if (collect($row)->filter(fn($value) => $value !== null && $value !== '')->isEmpty()) {
                continue;
            }
            $rowErrors = [];
            $data = [];

            foreach ($headers as $columnIndex => $header) {
                if ($header === '') {
                    continue;
                }

                $data[$header] = $row[$columnIndex] ?? null;
            }

            $clientCode = trim((string) ($data['client_code'] ?? ''));
            $serviceId = trim((string) ($data['service_id'] ?? ''));
            $serviceType = strtolower($data['service_type'] ?? 'direct');
            $hours = is_numeric($data['hours'] ?? null) ? (float) $data['hours'] : 0;

            if (!$clientCode && !$serviceId) {
                $rowErrors[] = "Row {$rowNumber}: client name, client id, and service id are required.";
                continue;
            }

            $client = Client::withTrashed()->where('id', $clientCode)->first();

            if (!$client) {
                $rowErrors[] = "Row {$rowNumber}: client with code {$clientCode} not found.";
            }

            $frontEmployeeId = trim((string) ($data['front_face_emp_code'] ?? ''));
            $workingEmployeeId = trim((string) ($data['working_emp_code']  ?? ''));

            $frontUser = $frontEmployeeId ? User::withTrashed()->where('id', $frontEmployeeId)->first() : null;
            $workingUser = $workingEmployeeId ? User::withTrashed()->where('id', $workingEmployeeId)->first() : null;

            if ($frontEmployeeId && !$frontUser) {
                $rowErrors[] = "Row {$rowNumber}: employee id {$frontEmployeeId} is not present in the system.";
            }

            if ($workingEmployeeId && !$workingUser) {
                $rowErrors[] = "Row {$rowNumber}: employee id {$workingEmployeeId} is not present in the system.";
            }

            if ($serviceType === 'direct') {

                $existsDirect = Service::where('service_id', $serviceId)
                    ->where('service_type', 'direct')
                    ->exists();

                if ($existsDirect) {
                    $rowErrors[] = "Row {$rowNumber}: direct service_id '{$serviceId}' already exists.";
                }

                if (isset($seenDirectServices[$serviceId])) {
                    $rowErrors[] = "Row {$rowNumber}: duplicate direct service_id '{$serviceId}' in file.";
                } else {
                    $seenDirectServices[$serviceId] = true;
                }
            }

            $dbHours = Service::where('service_id', $serviceId)->whereIn('service_type', ['direct', 'proxy'])->sum('hours');
            $excelHours = $serviceHoursTracker[$serviceId] ?? 0;
            $totalHours = $dbHours + $excelHours + $hours;
            if ($totalHours > 176) {
                $rowErrors[] = "Row {$rowNumber}: total hours for service_id '{$serviceId}' exceeds 176 (current total: {$dbHours}).";
            } else {
                $serviceHoursTracker[$serviceId] = $excelHours + $hours;
            }

            if (!empty($rowErrors)) {
                $errors = array_merge($errors, $rowErrors);
                continue;
            }

            $servicesToCreate[] = [
                'client_id' => $client->id,
                'service_type' => in_array($serviceType, ['direct', 'proxy', 'external']) ? $serviceType : 'direct',
                'service_id' => $serviceId,
                'hours' => $hours,
                'service_status' => in_array(strtolower($data['service_status'] ?? ''), ['new', 'in_progress', 'completed', 'replacement', 'canceled']) ? strtolower($data['service_status']) : 'new',
                'front_resource' => $frontUser?->id,
                'working_resource' => $workingUser?->id,
                'start_date' => $this->normalizeImportDate($data['start_date'] ?? null),
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

        DB::transaction(function () use ($servicesToCreate) {

            foreach ($servicesToCreate as $serviceData) {
                Service::create($serviceData);

                $memberIds = collect([$serviceData['front_resource'], $serviceData['working_resource']])
                    ->filter()
                    ->unique()
                    ->all();

                if (!empty($memberIds)) {
                    Client::find($serviceData['client_id'])?->teamProjects()->syncWithoutDetaching($memberIds);
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
        $this->authorizeClientAccess($client);

        $request->validate([
            'service_type' => 'required|in:direct,proxy,external',
            'service_id' => 'required',
            'hours' => 'required|numeric',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
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
            'hours',
            'month',
            'year',
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
            // ->whereMonth('created_at', $currentMonth)
            // ->whereYear('created_at', $currentYear)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->exists();

        if ($sameServiceExists) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'service_err' => 'This service already exists for this resource in the selected month.'
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
                // ->whereMonth('created_at', $currentMonth)
                // ->whereYear('created_at', $currentYear)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->exists();

            if ($directResourceExists) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'service_err' => 'This direct resource is already assigned to some service for selected month.'
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

        // dd($data);

        $client->services()->create($data);

        return redirect()
            ->route('clients.services.index', $client->id)
            ->with('success', 'Service created successfully');
    }

    public function servicesShow(Client $client, Service $service)
    {
        $this->authorize('service-show');
        $this->authorizeClientAccess($client);

        $service->load(['frontResource', 'workingResource']);
        return view('clients.services.show', compact('client', 'service'));
    }

    public function updateService(Request $request, Client $client, Service $service)
    {
        $this->authorize('service-edit');
        $this->authorizeClientAccess($client);

        $rules = [
            'service_type'      => 'required|in:direct,proxy,external',
            'service_id'        => 'required',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
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
            'hours',
            'month',
            'year',
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

        // if (!empty($service->created_at)) {
        //     $serviceMonth = $service->created_at->month;
        //     $serviceYear  = $service->created_at->year;
        // } else {
        //     $serviceMonth = null;
        //     $serviceYear  = null;
        // }

        $serviceMonth = $service->month ?? null;
        $serviceYear  = $service->year ?? null;

        $sameServiceExists = $client->services()
            ->where('service_id', $request->service_id)
            ->where('id', '!=', $service->id)
            ->where('working_resource', $request->working_resource)
            // ->whereMonth('created_at', $serviceMonth)
            // ->whereYear('created_at', $serviceYear)
            ->where('month', $serviceMonth)
            ->where('year', $serviceYear)
            ->exists();

        // dd($currentMonth);


        if ($sameServiceExists) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'service_err' => 'This service already exists for the same resources in the selected month.'
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
                ->where('service_id', $request->service_id)
                ->where('service_type', 'direct')
                ->where('id', '!=', $service->id)
                ->where('front_resource', $request->front_resource)
                // ->whereMonth('created_at', $serviceMonth)
                // ->whereYear('created_at', $serviceYear)
                ->where('month', $serviceMonth)
                ->where('year', $serviceYear)
                ->exists();

            if ($directResourceExists) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'service_err' => 'This direct resource is already assigned to some service for selected month.'
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
        $this->authorizeClientAccess($client);

        $service->delete();

        return back()->with('success', 'Service deleted successfully');
    }

    private function validateMonthlyServiceHours(Client $client, Request $request, ?Service $service = null)
    {
        // For updates, validate against the edited service's month/year bucket.
        // $validationDate = $service ? Carbon::parse($service->created_at) : Carbon::now();
        // $currentMonth = $validationDate->month;
        // $currentMonthName = $validationDate->format('F');
        // $currentYear = $validationDate->year;

        $currentMonth = $service->month ?? $request->month ?? now()->month;
        $currentYear  = $service->year ?? $request->year ?? now()->year;

        $currentMonthName = \Carbon\Carbon::create()
            ->month($currentMonth)
            ->format('F');

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
            // ->whereMonth('created_at', $currentMonth)
            // ->whereYear('created_at', $currentYear);
            ->where('month', $currentMonth)
            ->where('year', $currentYear);

        if ($service) {
            $serviceHoursQuery->where('id', '!=', $service->id);
        }

        $usedHoursForServiceId = (float) $serviceHoursQuery->sum('hours');
        $remainingHour = (float) $maxHours - $usedHoursForServiceId;

        $isSameServiceAndHours = $service
            && $service->service_id === $request->service_id
            && (float) $service->hours === (float) $request->hours;

        if (!$isSameServiceAndHours && (float) $request->hours > $remainingHour) {
            return back()->withInput()->withErrors([
                'hours' => "Only {$remainingHour} hours are available for service ID {$request->service_id} this month.",
            ]);
        }

        $workingResourceExistsQuery = $client->services()
            ->where('service_id', $request->service_id)
            ->where('working_resource', $request->working_resource)
            ->whereMonth('month', $currentMonth)
            ->whereYear('year', $currentYear);

        if ($service) {
            $workingResourceExistsQuery->where('id', '!=', $service->id);
        }

        if (!empty($request->working_resource) && $workingResourceExistsQuery->exists()) {
            return back()->withInput()->withErrors([
                'working_resource' => 'This working resource is already assigned to the same service ID in the current month.',
            ]);
        }

        $existingDirectServiceQuery = $client->services()
            ->where('service_id', $request->service_id)
            ->where('service_type', 'direct')
            ->whereMonth('month', $currentMonth)
            ->whereYear('year', $currentYear);

        if ($service) {
            $existingDirectServiceQuery->where('id', '!=', $service->id);
        }

        if ($request->service_type === 'direct' && $existingDirectServiceQuery->exists()) {
            return back()->withInput()->withErrors([
                'service_type' => 'Only one direct service type is allowed for this service ID in the current month. Use proxy for additional entries.',
            ]);
        }

        return null;
    }

    private function authorizeClientAccess(Client $client): void
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            return;
        }

        $isAssignedToClient = $client->allUsers()
            ->where('users.id', $user->id)
            ->exists();

        abort_unless($isAssignedToClient, 403);
    }

    public function addServicesTable(Request $request)
    {
        $this->authorize('project-show');

        $clients = Client::orderBy('name')->get(['id', 'name']);
        $employees = User::role('Employee')->orderBy('name')->get(['id', 'name', 'employee_id']);
        if ($employees->isEmpty()) {
            $employees = User::orderBy('name')->get(['id', 'name', 'employee_id']);
        }

        $selectedMonthStr = $request->get('month', now()->format('Y-m'));
        $parts = explode('-', $selectedMonthStr);
        $currentYear = isset($parts[0]) && is_numeric($parts[0]) ? (int)$parts[0] : now()->year;
        $currentMonth = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : now()->month;

        $selectedMonthStr = sprintf('%04d-%02d', $currentYear, $currentMonth);

        $services = Service::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->orderBy('id', 'asc')
            ->get();

        return view('services.index', compact(
            'clients',
            'employees',
            'currentMonth',
            'currentYear',
            'selectedMonthStr',
            'services'
        ));
    }

    public function saveServicesTable(Request $request)
    {
        $this->authorize('project-show');

        $selectedMonthStr = $request->input('selected_month', now()->format('Y-m'));
        $parts = explode('-', $selectedMonthStr);
        $selectedYear = isset($parts[0]) && is_numeric($parts[0]) ? (int)$parts[0] : now()->year;
        $selectedMonth = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : now()->month;

        $servicesData = $request->input('services', []);

        $savedCount = 0;
        DB::transaction(function () use ($servicesData, $selectedYear, $selectedMonth, &$savedCount, $request) {
            if ($request->has('deleted_service_ids')) {
                Service::whereIn('id', (array)$request->input('deleted_service_ids'))->delete();
            }

            foreach ($servicesData as $data) {
                if (empty($data['client_id']) && empty($data['service_id'])) {
                    continue;
                }

                $frontResource = !empty($data['front_resource']) ? $data['front_resource'] : null;
                $workingResource = ($data['service_type'] ?? '') === 'proxy'
                    ? (!empty($data['working_resource']) ? $data['working_resource'] : null)
                    : $frontResource;

                $status = $data['service_status'] ?? 'new';
                $comment = $data['comment'] ?? ($data['reason'] ?? null);
                $month = $selectedMonth;
                $year = $selectedYear;

                $servicePayload = [
                    'client_id' => $data['client_id'],
                    'service_type' => in_array($data['service_type'] ?? '', ['direct', 'proxy', 'external']) ? $data['service_type'] : 'direct',
                    'service_id' => $data['service_id'],
                    'hours' => !empty($data['hours']) ? $data['hours'] : 0,
                    'month' => $month,
                    'year' => $year,
                    'service_status' => in_array($status, ['new', 'in_progress', 'completed', 'replacement', 'canceled']) ? $status : 'new',
                    'front_resource' => $frontResource,
                    'working_resource' => $workingResource,
                    'start_date' => !empty($data['start_date']) ? $data['start_date'] : null,
                    'end_date' => in_array($status, ['completed', 'canceled']) && !empty($data['end_date']) ? $data['end_date'] : null,
                    'cancel_reason' => !empty($comment) ? $comment : null,
                    'replacement_reason' => null,
                ];

                if (!empty($data['id'])) {
                    $service = Service::find($data['id']);
                    if ($service) {
                        $service->update($servicePayload);
                        $savedCount++;
                    } else {
                        Service::create($servicePayload);
                        $savedCount++;
                    }
                } else {
                    Service::create($servicePayload);
                    $savedCount++;
                }

                $memberIds = collect([$frontResource, $workingResource])->filter()->unique()->all();
                if (!empty($memberIds) && !empty($data['client_id'])) {
                    Client::find($data['client_id'])?->teamProjects()->syncWithoutDetaching($memberIds);
                }
            }
        });

        $monthParam = sprintf('%04d-%02d', $selectedYear, $selectedMonth);
        return redirect()->route('services.add', ['month' => $monthParam])
            ->with('success', "Services saved successfully! ({$savedCount} record(s) processed)");
    }

    public function copyPreviousMonthServices(Request $request)
    {
        $this->authorize('project-show');

        // Target month (to_month or fallback to month)
        $targetMonthStr = $request->input('to_month', $request->input('month', now()->format('Y-m')));
        $toParts = explode('-', $targetMonthStr);
        $targetYear = isset($toParts[0]) && is_numeric($toParts[0]) ? (int)$toParts[0] : now()->year;
        $targetMonth = isset($toParts[1]) && is_numeric($toParts[1]) ? (int)$toParts[1] : now()->month;
        $targetDate = \Carbon\Carbon::createFromDate($targetYear, $targetMonth, 1);

        // Source month (from_month or fallback to previous month of target)
        if ($request->filled('from_month')) {
            $fromParts = explode('-', $request->input('from_month'));
            $fromYear = isset($fromParts[0]) && is_numeric($fromParts[0]) ? (int)$fromParts[0] : $targetYear;
            $fromMonth = isset($fromParts[1]) && is_numeric($fromParts[1]) ? (int)$fromParts[1] : $targetMonth;
            $fromDate = \Carbon\Carbon::createFromDate($fromYear, $fromMonth, 1);
        } else {
            $fromDate = (clone $targetDate)->subMonth();
            $fromYear = $fromDate->year;
            $fromMonth = $fromDate->month;
        }

        $targetMonthParam = sprintf('%04d-%02d', $targetYear, $targetMonth);

        // Check if source and target are the same month
        if ($fromYear === $targetYear && $fromMonth === $targetMonth) {
            return redirect()
                ->route('services.add', ['month' => $targetMonthParam])
                ->with('error', "Source month (" . $fromDate->format('F Y') . ") and target month cannot be the same.");
        }

        $sourceServices = Service::where('year', $fromYear)
            ->where('month', $fromMonth)
            ->get();

        if ($sourceServices->isEmpty()) {
            return redirect()
                ->route('services.add', ['month' => $targetMonthParam])
                ->with('warning', "No services found in source month (" . $fromDate->format('F Y') . ") to copy.");
        }

        $copiedCount = 0;
        DB::transaction(function () use ($sourceServices, $targetYear, $targetMonth, &$copiedCount) {
            foreach ($sourceServices as $s) {
                Service::updateOrCreate(
                    [
                        'client_id' => $s->client_id,
                        'service_id' => $s->service_id,
                        'month' => $targetMonth,
                        'year' => $targetYear,
                    ],
                    [
                        'service_type' => $s->service_type,
                        'hours' => $s->hours,
                        'service_status' => $s->service_status,
                        'front_resource' => $s->front_resource,
                        'working_resource' => $s->working_resource,
                        'start_date' => $s->start_date,
                        'end_date' => $s->end_date,
                        'cancel_reason' => $s->cancel_reason,
                        'replacement_reason' => $s->replacement_reason,
                    ]
                );

                $memberIds = collect([$s->front_resource, $s->working_resource])->filter()->unique()->all();
                if (!empty($memberIds) && !empty($s->client_id)) {
                    Client::find($s->client_id)?->teamProjects()->syncWithoutDetaching($memberIds);
                }

                $copiedCount++;
            }
        });

        return redirect()
            ->route('services.add', ['month' => $targetMonthParam])
            ->with('success', "Successfully copied {$copiedCount} service(s) from " . $fromDate->format('F Y') . " to " . $targetDate->format('F Y') . "!");
    }
}

