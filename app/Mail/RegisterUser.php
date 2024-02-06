<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegisterUser extends Mailable
{
    use Queueable, SerializesModels;
    public $mailData;
    public $svgCode;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData, $svgCode)
    {
        $this->mailData = $mailData;
        $this->svgCode = $svgCode;
        $this->subject('¡Bienvenido!');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.registerMailUser')
                    ->with(['mailData' => $this->mailData]);
                    // ->attachData($this->svgCode, 'codigoQR.svg', [
                    //     'mime' => 'image/svg+xml',
                    // ]);
    }
}
