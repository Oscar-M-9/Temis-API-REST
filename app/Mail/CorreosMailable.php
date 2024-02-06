<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CorreosMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $all_contenido;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($all_contenido,$asunto)
    {

        $this->all_contenido = $all_contenido;
        $this->subject($asunto);

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->view('dashboard.Email.correo')
        ->with(['all_contenido' => $this->all_contenido]);
    }
}
