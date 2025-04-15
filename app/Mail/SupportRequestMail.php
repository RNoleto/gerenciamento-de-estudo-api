<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data; // Dados validados do formulário

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        // Recebe os dados do formulário
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from('no-reply@estuday.com', 'Estuday')
            ->subject('Nova Solicitação de Suporte - Estuday')
            ->markdown('emails.support-request')
            ->with($this->data);
    }
}
