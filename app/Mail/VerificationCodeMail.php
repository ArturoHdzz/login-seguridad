<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Verification code for authentication.
     *
     * This is the unique code used for verifying the user's email address during registration or account verification.
     *
     * @var string
     */
    public $verificationCode;

    /**
     * Create a new message instance.
     *
     * This constructor accepts the verification code as a parameter and stores it 
     * in a public property for later use in the email view.
     *
     * @param string $verificationCode The verification code generated for the user
     * @return void
     */
    public function __construct(string $verificationCode)
    {
        // Store the verification code to be used in the view
        $this->verificationCode = $verificationCode;
    }

    /**
     * Get the envelope for the message.
     *
     * The envelope defines the subject and other meta information for the email.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Verification Code - Secure Registration'
        );
    }

    /**
     * Get the content definition for the message.
     *
     * The content method defines the email body by specifying the view to be used 
     * and passing any necessary data (in this case, the verification code).
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'mail.verification_code',  // The view that will be used for the email content
            with: [
                'code' => $this->verificationCode  // Pass the verification code to the view
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * This method defines any files that should be attached to the email. 
     * In this case, there are no attachments.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}