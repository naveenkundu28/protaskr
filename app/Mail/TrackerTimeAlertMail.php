<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrackerTimeAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $htmlContent;
    public $ccEmails;

    public function __construct($htmlContent, $ccEmails)
    {
        $this->htmlContent = $htmlContent;
        $this->ccEmails = $ccEmails;
    }

    public function build()
    {
        return $this->subject('Task Report (Last 24 Hours)')
                    ->cc($this->ccEmails)
                    ->view('emails.trackerTimeAlert')
                    ->with([
                        'htmlContent' => $this->htmlContent,
                    ]);
    }
}

