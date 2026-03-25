<?php

namespace App\Http\Controllers;

use App\Models\Hour;
use App\Models\Service;
use App\Models\Skill;
use App\Models\Tag;
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
        $tasksCount = $user->tasks()->where('status', '!=', 'completed')->count();
        $filesCount = $user->files()->count();
        $recentTasks = $user->tasks()->latest()->take(5)->get();
        
        $tagSummary = Tag::withCount([
                'users' => function ($query) {
                    $query->where('user_type', 'internal');
                }
            ])
            ->orderBy('name')
            ->get();
        
        $masterHours = Hour::where('year', $now->year)
            ->where('month', $now->format('F'))
            ->value('hours');
        
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

        if (!$masterHours) {
            $masterHours = null;
            return view('dashboard', compact(
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
            ));
        }

        $halfHours = $masterHours / 2;
        $employees = User::role('Employee')->get(['id']);
        $internalUsers = User::where('user_type', 'Internal')->get(['id']);
        $services = Service::whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->where('service_type', '!=' ,'external')
            ->get(['hours', 'front_resource', 'working_resource']);

        $hoursByUser = [];

        foreach ($services as $service) {
            $assignedUsers = collect([$service->working_resource])
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

        $benchUserMap = User::role('Employee')
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

        $profitLossSummary = [
            'total_hours' => $totalAvailableHours,
            'assigned_hours' => $assignedInternalHours,
            'loss_hours' => $lossHours,
            'loss_percentage' => $totalAvailableHours > 0 ? round(($lossHours / $totalAvailableHours) * 100, 2) : 0,
            'profit_hours' => $profitHours,
            'profit_percentage' => $totalAvailableHours > 0 ? round(($profitHours / $totalAvailableHours) * 100, 2) : 0,
            'internal_users_count' => $internalUsers->count(),
        ];

        return view('dashboard', compact(
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
        ));
    }

    public function home()
    {
        return redirect()->route('dashboard');
    }
}
