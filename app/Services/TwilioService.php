<?php
namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;
    protected $whatsappNumber;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.auth_token')
        );
        $this->whatsappNumber = config('services.twilio.whatsapp_number');
    }

    public function sendWhatsAppMessage($to, $message)
    {
        return $this->client->messages->create(
            'whatsapp:' . $to, // El número de destino, debe ser un número de WhatsApp
            [
                'from' => $this->whatsappNumber,
                'body' => $message
            ]
        );
    }
}
