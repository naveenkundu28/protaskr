<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Hour;
use App\Models\Project;
use App\Models\Service;
use App\Models\Skill;
use App\Models\Story;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TrackerHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        
        $user = Auth::user();
        $now = Carbon::now();

        if ($user->hasRole(\App\Support\Roles::RESOURCE)) {
            return view('dashboard.resources', $this->resourceDashboard($user, $now));
        }

        $tasksCount = $user->tasks()->where('status', '!=', 'completed')->count();
        $filesCount = $user->files()->count();
        $recentTasks = $user->tasks()->latest()->take(5)->get();
        
        $tagSummary = Tag::withCount([
                'users' => function ($query) {
                    $query->where('user_type', 'Internal')
                    ->whereDoesntHave('role', function ($q) {
                        $q->where('name', \App\Support\Roles::ADMIN);
                    });
                }
            ])
            ->orderBy('name')
            ->get();

        $totalResources = User::where('user_type', 'Internal')
                        ->whereDoesntHave('role', function ($q) {
                            $q->where('name', \App\Support\Roles::ADMIN);
                        })->count();

        $currentClients = Client::query()
            ->with(['users' => function ($query) {
                $query->withTrashed();
            }])
            ->whereNull('deleted_at')
            ->where('status', '!=', 'completed')
            ->where('type', 'client')
            ->count();
        
        $masterHours = Hour::where('year', $now->year)
            ->where('month', $now->format('F'))
            ->value('hours');
        
        $totalHours = $masterHours * $totalResources;
                
        $skillSetSummary = Skill::with(['primaryUsers' => function ($query) {
            $query->orderBy('name');
        }])
        ->orderBy('name')
        ->get()
        ->map(function ($skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'users' => $skill->primaryUsers->values(),
                'employee_id' => $skill->employee_id,
            ];
        })
        ->filter(fn ($skill) => $skill['users']->isNotEmpty())
        ->values();

        $benchUsersSummary = collect();

        $workingHoursSummary = [
            'FULL +' => 0,
            'FULL' => 0,
            'HALF +' => 0,
            'HALF' => 0,
            'ADHOC' => 0,
            'Bench' => 0,
        ];
        $profitLossSummary = [
            'total_hours' => 0,
            'assigned_hours' => 0,
            'loss_hours' => 0,
            'loss_percentage' => 0,
            'profit_hours' => 0,
            'profit_percentage' => 0,
            'internal_users_count' => 0,
        ];

        $halfHours = $masterHours / 2;
        $employees = User::ofRole(\App\Support\Roles::RESOURCE)->get(['id']);
        $internalUsers = User::where('user_type', 'Internal')
            ->whereDoesntHave('role', function ($q) {
                $q->where('name', \App\Support\Roles::ADMIN);
            })->get(['id']);
        $services = Service::where('year', $now->year)
            ->where('month', $now->month)
            ->where('service_type', '!=' ,'external')
            ->get(['id', 'hours', 'front_resource', 'working_resource']);

        $hoursByUser = [];

        foreach ($services as $service) {
            $assignedUsers = collect($service->working_resource_ids)
                ->filter()
                ->unique();

            foreach ($assignedUsers as $assignedUserId) {
                $hoursByUser[$assignedUserId] = ($hoursByUser[$assignedUserId] ?? 0) + (float) $service->hours;
            }
        }
        
        foreach ($internalUsers as $employee) {
            $assignedHours = $hoursByUser[$employee->id] ?? 0;
            $freeHours = max($masterHours - $assignedHours, 0);

            if ($assignedHours <= 0) {
                $workingHoursSummary['Bench']++;
            } elseif ($assignedHours > $masterHours) {
                $workingHoursSummary['FULL +']++;
            } elseif ($assignedHours == $masterHours) {
                $workingHoursSummary['FULL']++;
            } elseif ($assignedHours == $halfHours) {
                $workingHoursSummary['HALF']++;
            } elseif ($assignedHours > $halfHours && $assignedHours < $masterHours) {
                $workingHoursSummary['HALF +']++;
            } else {
                $workingHoursSummary['ADHOC']++;
            }
        }

        $benchUserMap = User::ofRole(\App\Support\Roles::RESOURCE)
            ->with('primarySkills')
            ->where('user_type', 'Internal')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($user) use ($hoursByUser, $masterHours) {
                $assignedHours = (float) ($hoursByUser[$user->id] ?? 0);
                $freeHours = max($masterHours - $assignedHours, 0);

                return [
                    $user->id => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'employee_id' => $user->employee_id,
                        'free_hours' => round($freeHours, 2),
                    ]
                ];
            });

        $benchUsersSummary = Skill::with(['primaryUsers' => function ($query) {
                $query->where('user_type', 'Internal')->orderBy('name');
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($skill) use ($benchUserMap) {
                $users = $skill->primaryUsers
                    ->map(function ($user) use ($benchUserMap) {
                        return $benchUserMap->get($user->id);
                    })
                    ->filter(fn ($user) => $user && $user['free_hours'] >= 20)
                    ->values();

                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'users' => $users,
                ];
            })
            ->filter(fn ($skill) => $skill['users']->isNotEmpty())
            ->values();

        $internalUserIds = $internalUsers->pluck('id')->all();
        $assignedInternalHours = collect($hoursByUser)
            ->only($internalUserIds)
            ->sum();
        $totalAvailableHours = $masterHours * $internalUsers->count();        
        $lossHours = max($totalAvailableHours - $assignedInternalHours, 0);
        $profitHours = max($assignedInternalHours - $totalAvailableHours, 0);

        $totalFreeHours = max($totalHours - $assignedInternalHours, 0);

        $profitLossSummary = [
            'total_hours' => $totalAvailableHours,
            'assigned_hours' => $assignedInternalHours,
            'loss_hours' => $lossHours,
            'loss_percentage' => $totalAvailableHours > 0 ? round(($lossHours / $totalAvailableHours) * 100, 2) : 0,
            'profit_hours' => $profitHours,
            'profit_percentage' => $totalAvailableHours > 0 ? round(($profitHours / $totalAvailableHours) * 100, 2) : 0,
            'internal_users_count' => $internalUsers->count(),
        ];
        
        if ($user->hasRole(\App\Support\Roles::CLIENT)) {
            return view('dashboard.clients', $this->clientDashboard($user));
        }

        if (!$masterHours) {
            $masterHours = null;
            return view('dashboard.admin', compact(
                'tasksCount',
                'filesCount',
                'recentTasks',
                'tagSummary',
                'masterHours',
                'skillSetSummary',
                'benchUsersSummary',
                'workingHoursSummary',
                'profitLossSummary',
                'now',
                'totalResources',
                'currentClients',
                'totalHours',
                'totalFreeHours'
            ));
        }

        return view('dashboard.admin', compact(
            'tasksCount',
            'filesCount',
            'recentTasks',
            'tagSummary',
            'masterHours',
            'skillSetSummary',
            'benchUsersSummary',
            'workingHoursSummary',
            'profitLossSummary',
            'now',                
            'totalResources',
            'currentClients',
            'totalHours',
            'totalFreeHours'
        ));
    }

    public function home()
    {
        return redirect()->route('dashboard');
    }
    /**
     * Figures for the resource dashboard. Everything is limited to the logged-in
     * resource: the clients and projects they are on, and the tasks assigned to
     * or created by them.
     */
    private function resourceDashboard(User $user, Carbon $now): array
    {
        $monthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $monthStart->copy()->subMonthNoOverflow();
        // the same number of days into last month, so a part month is compared like for like
        $lastMonthSameDay = $lastMonthStart->copy()->addSeconds($monthStart->diffInSeconds($now));

        // percentage change, or null when there is nothing to compare against
        $change = fn ($current, $previous) => $previous > 0 ? (int) round(($current - $previous) / $previous * 100) : null;

        $myTasks = fn () => Task::query()
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('created_by', $user->id))
            ->whereHas('project', fn ($q) => $q->whereNull('deleted_at'));

        // Top cards
        $clientsCount = $user->projectMembers()->where('status', '!=', 'completed')->count();

        $projectsCount = Project::whereHas('members', fn ($q) => $q->where('users.id', $user->id))->count();
        $projectsBeforeThisMonth = Project::whereHas(
            'members',
            fn ($q) => $q->where('users.id', $user->id)->where('project_members.created_at', '<', $monthStart)
        )->count();

        $openTasksCount = $myTasks()->whereNotIn('status', ['completed', 'closed'])->count();

        $secondsThisMonth = (int) TrackerHistory::where('user_id', $user->id)
            ->whereBetween('start_time', [$monthStart, $now])->sum('elapsed_time');
        $secondsLastMonth = (int) TrackerHistory::where('user_id', $user->id)
            ->whereBetween('start_time', [$lastMonthStart, $lastMonthSameDay])->sum('elapsed_time');
        $assignedHours = (float) Task::where('user_id', $user->id)
            ->whereBetween('created_at', [$monthStart, $now])
            ->whereHas('project', fn ($q) => $q->whereNull('deleted_at'))
            ->sum('estimated_hours');

        // Small bar graph on each card, drawn to match the card's month-on-month change: rising bars for
        // an increase, falling bars for a decrease, and flat grey bars when there is nothing to compare with.
        $shape = fn ($trend) => match (true) {
            $trend === null || $trend === false => ['kind' => 'none', 'bars' => [38, 38, 38, 38, 38, 38, 38]],
            $trend >= 0 => ['kind' => 'up', 'bars' => [28, 38, 34, 50, 58, 72, 90]],
            default => ['kind' => 'down', 'bars' => [90, 74, 78, 56, 48, 36, 26]],
        };
        $hoursTrend = $change($secondsThisMonth, $secondsLastMonth);

        $stats = [
            // ['icon' => 'bi-people-fill', 'tone' => 'resources', 'label' => 'Clients', 'value' => $clientsCount, 'trend' => false,
            //     'spark' => $shape(false)],
            // ['icon' => 'bi-bullseye', 'tone' => 'clients', 'label' => 'Projects', 'value' => $projectsCount,
            //     'trend' => $change($projectsCount, $projectsBeforeThisMonth),
            //     'spark' => $shape($change($projectsCount, $projectsBeforeThisMonth))],
            ['icon' => 'bi-card-checklist', 'tone' => 'resources', 'label' => 'Open Tasks', 'value' => $openTasksCount, 'trend' => false,
                'spark' => $shape(false)],
            ['icon' => 'bi-hourglass-split', 'tone' => 'clients', 'label' => 'Billed Hours',
                'value' => 0, 'trend' => false,
                'spark' => $shape(false)],
            ['icon' => 'bi-hourglass-split', 'tone' => 'free-hours', 'label' => 'Assigned Hours',
                'value' => rtrim(rtrim(number_format($assignedHours, 1), '0'), '.'), 'trend' => false,
                'spark' => $shape(false)],
                
            ['icon' => 'bi-clock', 'tone' => 'total-hours', 'label' => 'Hours (This Month)',
                'value' => rtrim(rtrim(number_format($secondsThisMonth / 3600, 1), '0'), '.'),
                'trend' => $hoursTrend,
                'spark' => $shape($hoursTrend)],
        ];

        // Task status: every task still open, plus the ones finished this month
        $open = $myTasks()->whereNotIn('status', ['completed', 'closed'])
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $completedThisMonth = $myTasks()->whereIn('status', ['completed', 'closed'])
            ->where('updated_at', '>=', $monthStart)->count();

        $taskStatuses = [
            ['label' => 'To Do', 'count' => (int) ($open['to_do'] ?? 0), 'color' => '#3d9bf5'],
            ['label' => 'In Progress', 'count' => (int) ($open['in_progress'] ?? 0), 'color' => '#2f6fe4'],
            ['label' => 'In Review', 'count' => (int) ($open['in_review'] ?? 0), 'color' => '#f7b23b'],
            ['label' => 'Completed', 'count' => $completedThisMonth, 'color' => '#22c58b'],
            ['label' => 'On Hold', 'count' => (int) ($open['on_hold'] ?? 0), 'color' => '#f0464f'],
        ];
        // any other open status (for example "pending") is counted with To Do
        $taskStatuses[0]['count'] += (int) $open->except(['to_do', 'in_progress', 'in_review', 'on_hold'])->sum();
        $taskTotal = array_sum(array_column($taskStatuses, 'count'));

        // Project progress: share of each project's tasks that are completed
        $palette = [
            ['dot' => '#a78bfa', 'bar' => '#2f80ed'],
            ['dot' => '#f472b6', 'bar' => '#22c58b'],
            ['dot' => '#fb923c', 'bar' => '#f7b23b'],
            ['dot' => '#6ee7b7', 'bar' => '#f76c8f'],
            ['dot' => '#93c5fd', 'bar' => '#8b5cf6'],
        ];
        $projectProgress = Project::whereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->withCount(['tasks', 'tasks as completed_tasks_count' => fn ($q) => $q->where('status', 'completed')])
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->values()
            ->map(fn ($project, $index) => [
                'id' => $project->id,
                'name' => $project->name,
                'progress' => $project->tasks_count ? (int) round($project->completed_tasks_count * 100 / $project->tasks_count) : 0,
            ] + $palette[$index % count($palette)])
            ->all();

        return compact('stats', 'taskStatuses', 'taskTotal', 'projectProgress', 'projectsCount');
    }

    /**
     * Figures for the client dashboard, limited to the client's delivery
     * projects and tasks which belong to stories in those projects.
     */
    private function clientDashboard(User $user): array
    {
        $clientProjects = fn () => Project::query()
            ->whereHas('client', fn ($query) => $query->where('profile_user_id', $user->id));

        $clientTasks = fn () => Task::query()
            ->whereNotNull('story_id')
            ->whereHas('story', fn ($query) => $query->whereHas(
                'project',
                fn ($project) => $project->whereHas(
                    'client',
                    fn ($client) => $client->where('profile_user_id', $user->id)
                )
            ));

        $projectsCount = $clientProjects()->count();
        $storiesCount = Story::query()->whereHas('project', fn ($query) => $query->whereHas(
            'client', fn ($client) => $client->where('profile_user_id', $user->id)
        ))->count();
        $openTasksCount = $clientTasks()->whereIn('status', ['to_do', 'in_progress'])->count();
        $onHoldTasksCount = $clientTasks()->where('status', 'on_hold')->count();

        $shape = fn () => ['kind' => 'none', 'bars' => [38, 38, 38, 38, 38, 38, 38]];
        $stats = [
            ['icon' => 'bi-kanban-fill', 'tone' => 'clients', 'label' => 'No. of Projects', 'value' => $projectsCount, 'trend' => false, 'spark' => $shape()],
            ['icon' => 'bi-journal-text', 'tone' => 'resources', 'label' => 'No. of Stories', 'value' => $storiesCount, 'trend' => false, 'spark' => $shape()],
            ['icon' => 'bi-card-checklist', 'tone' => 'free-hours', 'label' => 'Open Tasks', 'value' => $openTasksCount, 'trend' => false, 'spark' => $shape()],
            ['icon' => 'bi-pause-circle-fill', 'tone' => 'total-hours', 'label' => 'Block / Hold Tasks', 'value' => $onHoldTasksCount, 'trend' => false, 'spark' => $shape()],
        ];

        $statusCounts = $clientTasks()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $taskStatuses = [
            ['label' => 'To Do', 'count' => (int) ($statusCounts['to_do'] ?? 0), 'color' => '#3d9bf5'],
            ['label' => 'In Progress', 'count' => (int) ($statusCounts['in_progress'] ?? 0), 'color' => '#2f6fe4'],
            ['label' => 'In Review', 'count' => (int) ($statusCounts['in_review'] ?? 0), 'color' => '#f7b23b'],
            ['label' => 'Completed', 'count' => (int) (($statusCounts['completed'] ?? 0) + ($statusCounts['closed'] ?? 0)), 'color' => '#22c58b'],
            ['label' => 'On Hold', 'count' => (int) ($statusCounts['on_hold'] ?? 0), 'color' => '#f0464f'],
        ];
        $taskStatuses[0]['count'] += (int) $statusCounts->except(['to_do', 'in_progress', 'in_review', 'completed', 'closed', 'on_hold'])->sum();
        $taskTotal = array_sum(array_column($taskStatuses, 'count'));

        $palette = [
            ['dot' => '#a78bfa', 'bar' => '#2f80ed'], ['dot' => '#f472b6', 'bar' => '#22c58b'],
            ['dot' => '#fb923c', 'bar' => '#f7b23b'], ['dot' => '#6ee7b7', 'bar' => '#f76c8f'],
            ['dot' => '#93c5fd', 'bar' => '#8b5cf6'],
        ];
        $projectProgress = $clientProjects()
            ->withCount(['tasks', 'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'completed')])
            ->latest('updated_at')->take(5)->get()->values()
            ->map(fn ($project, $index) => [
                'id' => $project->id,
                'name' => $project->name,
                'progress' => $project->tasks_count ? (int) round($project->completed_tasks_count * 100 / $project->tasks_count) : 0,
            ] + $palette[$index % count($palette)])->all();

        return compact('stats', 'taskStatuses', 'taskTotal', 'projectProgress', 'projectsCount') + ['isClientDashboard' => true];
    }
}
