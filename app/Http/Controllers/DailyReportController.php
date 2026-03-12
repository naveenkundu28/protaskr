<?php

namespace App\Http\Controllers;

use App\Models\DailyPerformance;
use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {

        if($request->month){
            [$year, $month] = explode('-', $request->month);
            // $month = $request->month ?? date('m');
            // $year = $request->year ?? date('Y');
        }else{
            $month = date('m');
            $year = date('Y');
        }

        $users = User::orderBy('created_at')->get();
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
}
