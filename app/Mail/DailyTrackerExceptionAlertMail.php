<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyTrackerExceptionAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Carbon $reportDate;

    public function __construct(User $user, Carbon $reportDate)
    {
        $this->user = $user;
        $this->reportDate = $reportDate;
    }

    public function build()
    {
        return $this->subject('Daily Tracker Alert - '.$this->user->name)
            ->view('emails.dailyTrackerExceptionAlert');
    }
}
