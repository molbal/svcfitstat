<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ErrorMessage extends Mailable
{
    use Queueable, SerializesModels;

    /** @var string
     */
    public $text;

    /** @var string */
    public $subject;

    /**
     * ErrorMessage constructor.
     *
     * @param string $text
     * @param string $subject
     * @param string $to
     */
    public function __construct(string $text, string $subject) {
        $this->text = $text;
        $this->subject = $subject;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.error', ["message" => $this->text])
            ->subject($this->subject)
            ->from(env("MAIL_FROM_ADDRESS"));
    }
}
