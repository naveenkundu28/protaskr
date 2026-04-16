<?php

namespace App\Http\Controllers;

use App\Models\DailyPerformance;
use App\Models\DailyReport;
use App\Models\Tag;
use App\Models\TrackerHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use \App\Traits\TrackerTrait;
use Carbon\Carbon;

class DailyReportController extends Controller
{
    use TrackerTrait;
    public function index(Request $request)
    {
        $this->authorize('role-show');
        if ($request->month) {
            [$year, $month] = explode('-', $request->month);
            // $month = $request->month ?? date('m');
            // $year = $request->year ?? date('Y');
        } else {
            $month = date('m');
            $year = date('Y');
        }

        $users = User::whereDoesntHave('roles', function ($q) {
            $q->where('name', 'Admin');
        })->where('is_show_report', true)->orderBy('created_at')->get();
        $performances = DailyPerformance::all();

        $reports = DailyReport::with(['performance', 'user'])
            ->whereHas('user', function ($q) {
                $q->where('is_show_report', 1);
            })
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy(['date', 'user_id']);

        $dates = [];

        $totals = DailyReport::selectRaw('user_id, SUM(time) as total_minutes')
            ->whereHas('user', function ($q) {
                $q->where('is_show_report', 1);
            })
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->groupBy('user_id')
            ->pluck('total_minutes', 'user_id');

        $days = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);

        for ($i = 1; $i <= $days; $i++) {

            $dates[] = date('Y-m-d', strtotime("$year-$month-$i"));
        }

        return view('reports.hourly-task', compact('users', 'reports', 'dates', 'month', 'year', 'performances', 'totals'));
    }

    public function update(Request $request)
    {
        $this->authorize('role-show');
        abort_unless(Auth::user()?->hasRole('Admin'), 403);

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'hours' => 'required|integer|min:0|max:23',
            'minutes' => 'required|integer|min:0|max:59',
        ]);

        $totalMinutes = ((int) $data['hours'] * 60) + (int) $data['minutes'];
        $totalHours = $totalMinutes / 60;

        $performance = DailyPerformance::where('min', '<=', $totalHours)
            ->where('max', '>=', $totalHours)
            ->first();

        DailyReport::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'date' => $data['date'],
            ],
            [
                'time' => $totalMinutes,
                'daily_performance_id' => $performance?->id,
            ]
        );

        return redirect()
            ->route('daily.report', ['month' => date('Y-m', strtotime($data['date']))])
            ->with('success', 'Daily report updated successfully.');
    }

    public function generateWeekly(Request $request)
    {
        $this->authorize('role-show');
        abort_unless(Auth::user()?->hasRole('Admin'), 403);

        $dateFrom = $request->report_date ?: 'week';

        Artisan::call('app:generate-reports', [
            '--dateFrom' => $dateFrom,
        ]);

        if ($dateFrom !== 'week') {
            // calculate difference in days
            $days = Carbon::parse($dateFrom)->diffInDays(now());

            // safety (if same day selected)
            $days = $days == 0 ? 1 : $days;

            $message = "Daily report fetched for the last {$days} days.";
        } else {
            $message = "Daily report fetched for the last 7 days.";
        }

        return redirect()
            ->route('daily.report', ['month' => now()->format('Y-m')])
            ->with('success', $message);
    }


    public function getDailyTaskReports()
    {
        $this->authorize('role-show');
        $tags = Tag::all();
        return view('reports.daily-task', compact('tags'));
    }

    public function getUserTasks(Request $request)
    {
        $date = $request->tasksDate ?? now()->subDay()->format('Y-m-d');
        $tagId = $request->tag_id;

        $performances = DailyPerformance::orderBy('min', 'desc')->get();

        $users = User::where('user_type', 'Internal')->where('is_show_report', true)
            ->when($tagId, function ($q) use ($tagId) {
                $q->whereHas('tags', function ($q2) use ($tagId) {
                    $q2->where('tags.id', $tagId);
                });
            })
            ->get();

        $userIds = $users->pluck('id')->toArray();

        // Get tracker history only for selected date
        $trackerSums = TrackerHistory::selectRaw('user_id, task_id, SUM(elapsed_time) as total_seconds')
            ->whereIn('user_id', $userIds)
            ->whereDate('start_time', $date) // OR start_time if that is your actual tracking date
            ->groupBy('user_id', 'task_id')
            ->get();

        // Group by user
        $trackerByUser = $trackerSums->groupBy('user_id');

        // Get all task IDs which were actually tracked that day
        $taskIds = $trackerSums->pluck('task_id')->unique()->values();

        // Load tasks once
        $tasks = \App\Models\Task::with('project')
            ->whereIn('id', $taskIds)
            ->get()
            ->keyBy('id');

        $users = $users->map(function ($user) use ($trackerByUser, $tasks, $performances) {
            $userTrackers = $trackerByUser->get($user->id, collect());

            $totalSeconds = 0;
            $userTasks = collect();

            foreach ($userTrackers as $tracker) {
                $task = $tasks->get($tracker->task_id);

                if (!$task) {
                    continue;
                }

                $taskClone = clone $task;
                $taskClone->total_task_seconds = (int) $tracker->total_seconds;
                $taskClone->total_task_time = $this->_getTotalTime((int) $tracker->total_seconds);

                $userTasks->push($taskClone);

                $totalSeconds += (int) $tracker->total_seconds;
            }

            $user->setRelation('tasks', $userTasks);

            $user->total_time_seconds = $totalSeconds;
            $user->total_time = $this->_getTotalTime($totalSeconds);

            $totalHours = round($totalSeconds / 3600, 2);

            if ($userTasks->isEmpty()) {
                $user->performance = [
                    'title' => 'Absent',
                    'background_color' => '#8b1c1c',
                    'font_color' => '#ffffff',
                ];
            } elseif ($totalSeconds <= 0) {
                $user->performance = [
                    'title' => "Didn't Track",
                    'background_color' => '#8b1c1c',
                    'font_color' => '#ffffff',
                ];
            } else {
                $performance = $performances->first(function ($item) use ($totalHours) {
                    return $item->min <= $totalHours && $item->max > $totalHours;
                });

                $user->performance = $performance ? [
                    'title' => $performance->title,
                    'background_color' => $performance->background_color,
                    'font_color' => $performance->font_color,
                ] : [
                    'title' => 'Wrong Entry',
                    'background_color' => '#002589',
                    'font_color' => '#F5F5F5',
                ];
            }

            return $user;
        })->sortByDesc('total_time_seconds')->values();

        return response()->json([
            'status' => true,
            'data' => $users
        ]);
    }
}
