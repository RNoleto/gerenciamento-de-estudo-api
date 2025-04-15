<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SuporteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data; // dados do formulário

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->markdown('emails.suporte')
            ->subject('Nova mensagem de suporte - Estuday')
            ->with([
                'name' => $this->data['name'],
                'email' => $this->data['email'],
                'subject' => $this->data['subject'],
                'message' => $this->data['message'],
            ]);

    }
}

