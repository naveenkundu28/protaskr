<?php

namespace App\Console\Commands;

use App\Models\DailyPerformance;
use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDailyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-reports
                            {--dateFrom= : Start date in d-m-Y or Y-m-d format, or keyword: week, month, year}';

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
        [$dateFrom, $dateTo] = $this->resolveDateRange();

        $this->info("Generating daily reports from {$dateFrom->toDateString()} to {$dateTo->toDateString()}...");

        $records = DB::table('tracker_histories')
            ->select(
                'user_id',
                DB::raw('DATE(start_time) as work_date'),
                DB::raw('SUM(elapsed_time) as total_seconds')
            )
            ->whereNotNull('end_time')
            ->whereBetween(DB::raw('DATE(start_time)'), [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->groupBy('user_id', 'work_date')
            ->orderBy('work_date')
            ->get();

        foreach ($records as $row) {

            $minutes = floor($row->total_seconds / 60);

            $hours = $minutes / 60;

            $performance = DailyPerformance::where('min', '<=', $hours)
                ->where('max', '>=', $hours)
                ->first();

            DailyReport::where('user_id', '!=', $row->user_id)
                ->where('date', '!=', $row->work_date)
                ->updateOrCreate(
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
        return self::SUCCESS;
    }

    private function resolveDateRange(): array
    {
        $dateFromInput = $this->option('dateFrom');

        if (!$dateFromInput) {
            $yesterday = Carbon::yesterday();
            return [$yesterday->copy()->startOfDay(), $yesterday->copy()->endOfDay()];
        }

        $dateFrom = $this->parseDateFromOption($dateFromInput)->startOfDay();
        $dateTo = Carbon::today()->endOfDay();

        if ($dateFrom->gt($dateTo)) {
            $this->error('The dateFrom option must be earlier than or equal to today.');
            exit(self::FAILURE);
        }

        return [$dateFrom, $dateTo];
    }

    private function parseDateFromOption(string $value): Carbon
    {
        $normalizedValue = strtolower(trim($value));

        if ($normalizedValue === 'week') {
            return Carbon::today()->subDays(6);
        }

        if ($normalizedValue === 'month') {
            return Carbon::today()->startOfMonth();
        }

        if ($normalizedValue === 'year') {
            return Carbon::today()->startOfYear();
        }

        foreach (['d-m-Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable $e) {
            }
        }

        $this->error("Invalid dateFrom value [{$value}]. Use d-m-Y, Y-m-d, or one of: week, month, year.");
        exit(self::FAILURE);
    }
}
