<?php

namespace App\Console\Commands;

use App\Mail\DailyTrackerExceptionAlertMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DailyTrackerExceptionAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:daily-tracker-exception-alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily alerts for users with zero or excessive tracked hours.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $yesterday = Carbon::yesterday('Asia/Kolkata');

        if ($yesterday->isWeekend()) {
            $this->info('Weekend detected. Daily tracker exception alerts skipped.');
            return self::SUCCESS;
        }

        $managerEmails = [
            // 'anshulsehgal@virtualemployee.com',
            'naveenkundu@virtualemployee.com',
            // 'moizcutpiecewala@virtualemployee.com',
        ];

        $trackedSecondsByUser = DB::table('tracker_histories')
            ->select('user_id', DB::raw('SUM(elapsed_time) as total_seconds'))
            ->whereDate('start_time', $yesterday->toDateString())
            ->whereNotNull('end_time')
            ->groupBy('user_id')
            ->pluck('total_seconds', 'user_id');

        $users = User::role('Employee')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $flaggedUsers = $users->map(function ($user) use ($trackedSecondsByUser) {
            if (strcasecmp((string) $user->email, 'naveenkundu@virtualemployee.com') === 0) {
                return null;
            }

            $totalSeconds = (int) ($trackedSecondsByUser[$user->id] ?? 0);
            $totalHours = round($totalSeconds / 3600, 2);

            if ($totalSeconds === 0) {
                $user->tracked_hours = $totalHours;
                $user->alert_reason = 'absent';
                $user->question_text = 'Why were you absent yesterday? Please share the reason.';
                return $user;
            }

            if ($totalSeconds >= (13 * 3600)) {
                $user->tracked_hours = $totalHours;
                $user->alert_reason = 'excess';
                $user->question_text = 'Why did you track more than 13 hours yesterday? Please share the reason.';
                return $user;
            }

            return null;
        })->filter();

        foreach ($flaggedUsers as $user) {
            $toEmails = $managerEmails;

            if (!empty($user->email)) {
                $toEmails[] = $user->email;
                // $toEmails[] = 'moizcutpiecewala@virtualemployee.com';
            }

            Mail::to(array_values(array_unique($toEmails)))
                ->send(new DailyTrackerExceptionAlertMail($user, $yesterday));
        }

        $this->info("Daily tracker exception alerts sent: {$flaggedUsers->count()}");

        return self::SUCCESS;
    }
}
