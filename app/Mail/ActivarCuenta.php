<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivarCuenta extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $informacion;
    protected $url;
   
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $informacion, $url)
    {
        $this->user = $user;
        $this->informacion = $informacion;
        $this->url = $url;
    }
    
       
    

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'User Mail',
            from: $this->user->email,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'mails.correo',
            with: [
                'user' => $this->user,
                'informacion' => $this->informacion,
                'url' => $this->url
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
