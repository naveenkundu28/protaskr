<?php

namespace App\Console\Commands;

use App\Models\DailyPerformance;
use App\Models\DailyReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDailyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating historical daily reports...');
        $date = now()->subDay()->toDateString();
        $records = DB::table('tracker_histories')
            ->select(
                'user_id',
                DB::raw('DATE(start_time) as work_date'),
                DB::raw('SUM(elapsed_time) as total_seconds')
            )
            ->whereDate('start_time',$date)
            ->whereNotNull('end_time')
            ->groupBy('user_id','work_date')
            ->orderBy('work_date')
            ->get();

        foreach ($records as $row) {

            $minutes = floor($row->total_seconds / 60);

            $hours = $minutes / 60;

            $performance = DailyPerformance::where('min','<=',$hours)
                ->where('max','>=',$hours)
                ->first();

            DailyReport::updateOrCreate(
                [
                    'user_id' => $row->user_id,
                    'date' => $row->work_date
                ],
                [
                    'time' => $minutes,
                    'daily_performance_id' => $performance?->id
                ]
            );
        }

        $this->info('Daily reports generated successfully');
    }
}
