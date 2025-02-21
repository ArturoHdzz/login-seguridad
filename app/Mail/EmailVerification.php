<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The URL for email verification.
     *
     * @var string
     */
    public $url;

    /**
     * The name of the recipient.
     *
     * @var string
     */
    public $name;

    /**
     * Create a new message instance.
     *
     * @param string $url
     * @param string $name
     * @return void
     */
    public function __construct($url, $name)
    {
        // Assign URL and name to the public properties
        $this->url = $url;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * This method defines the content and subject of the email message.
     *
     * @return \Illuminate\Mail\Mailable
     */
    public function build()
    {
        // Configure the email view and subject
        return $this->view('emails.verification')
                    ->subject('Verify your account - Valid for 24 hours');
    }
}