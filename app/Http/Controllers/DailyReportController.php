<?php

namespace App\Http\Controllers;

use App\Models\DailyPerformance;
use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('role-show');
        if($request->month){
            [$year, $month] = explode('-', $request->month);
            // $month = $request->month ?? date('m');
            // $year = $request->year ?? date('Y');
        }else{
            $month = date('m');
            $year = date('Y');
        }

        $users = User::whereDoesntHave('roles', function ($q) {$q->where('name', 'Admin');})->orderBy('created_at')->get();
        $performances = DailyPerformance::all();

        $reports = DailyReport::with('performance')
            ->whereMonth('date',$month)
            ->whereYear('date',$year)
            ->get()
            ->groupBy(['date','user_id']);

        $dates = [];

        $totals = DailyReport::selectRaw('user_id, SUM(time) as total_minutes')
            ->whereMonth('date',$month)
            ->whereYear('date',$year)
            ->groupBy('user_id')
            ->pluck('total_minutes','user_id');

        $days = cal_days_in_month(CAL_GREGORIAN,(int)$month,(int)$year);

        for( $i = 1 ; $i <= $days ; $i++ ){

            $dates[] = date('Y-m-d',strtotime("$year-$month-$i"));

        }

        return view('reports.daily',compact('users', 'reports', 'dates', 'month', 'year', 'performances', 'totals'));
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
}
