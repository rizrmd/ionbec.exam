<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordTestTaker extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $mailData;
    public $client;

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
        
        // Get the current client from the app container or session
        $this->client = app()->has('current_client') ? app('current_client') : null;
        
        // If not available in app container, try to get from session
        if (!$this->client && session()->has('client_id')) {
            $this->client = Client::find(session('client_id'));
        }
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
