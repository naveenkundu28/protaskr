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
        })->orderBy('created_at')->get();
        $performances = DailyPerformance::all();

        $reports = DailyReport::with('performance')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy(['date', 'user_id']);

        $dates = [];

        $totals = DailyReport::selectRaw('user_id, SUM(time) as total_minutes')
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
        $users = User::where('user_type', 'Internal')
            ->when($tagId, function ($q) use ($tagId) {
                $q->whereHas('tags', function ($q2) use ($tagId) {
                    $q2->where('tags.id', $tagId);
                });
            })
            ->with(['tasks' => function ($q) use ($date) {
                $q->whereDate('created_at', $date)
                    ->with('project'); // client
            }])
            ->get();


        $users->map(function ($user) {

                $totalSeconds = 0;
                $performances = DailyPerformance::all();
                if ($user->tasks->isNotEmpty()) {

                foreach ($user->tasks as $task) {

                    $taskSeconds = TrackerHistory::query()
                        ->where('user_id', $user->id)
                        ->where('task_id', $task->id)
                        ->sum('elapsed_time');

                    $task->total_task_time = $this->_getTotalTime($taskSeconds);

                    $totalSeconds += $taskSeconds;
                }
            }

                $user->total_time = $this->_getTotalTime($totalSeconds);
                $totalHours = $totalSeconds / 3600;
                $performance = $performances->first(function ($item) use ($totalHours) {
                    return $item->min <= $totalHours && $item->max >= $totalHours;
                });

                $user->performance = $performance ? [
                    'title' => $performance->title,
                    'background_color' => $performance->background_color,
                    'font_color' => $performance->font_color,
                ] : [
                    'title' => 'Absent',
                    'background_color' => '#8b1c1c',
                    'font_color' => '#ffffff',
                ];

            return $user;
        });

        return response()->json([
            'status' => true,
            'data' => $users
        ]);
    }
}
