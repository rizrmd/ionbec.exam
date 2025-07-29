<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordTestTaker extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $mailData;

    /**
     * Create a new message instance.
     *
     * @param mixed $mailData
     *
     * @return void
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New Password Candidate')
            ->view('emails.reset-password-test-taker');
    }
}
