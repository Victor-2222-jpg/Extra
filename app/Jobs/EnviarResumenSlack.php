<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Juego;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnviarResumenSlack implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $juegoId;

    public function __construct($juegoId)
    {
        $this->juegoId = $juegoId;
    }

    public function handle()
    {
        try {
            $webhookUrl = 'https://hooks.slack.com/services/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';
            
            if (!$webhookUrl) {
                throw new \Exception('URL del webhook de Slack no configurada');
            }

            $juego = Juego::with(['user', 'historial'])->find($this->juegoId);
            
            $resumen = "*Resumen de Juego #{$this->juegoId}*\n";
            $resumen .= "Usuario: {$juego->user->name}\n";
            $resumen .= "Palabra: {$juego->palabra}\n\n";
            
            foreach ($juego->historial as $movimiento) {
                $resultado = $movimiento->acierto ? '✅' : '❌';
                $resumen .= "{$resultado} Letra: {$movimiento->letra} | ";
                $resumen .= "Palabra: {$movimiento->palabra_actual} | ";
                $resumen .= "Intentos: {$movimiento->intentos_restantes}\n";
            }

            $response = Http::post($webhookUrl, [
                'text' => $resumen
            ]);

            if (!$response->successful()) {
                throw new \Exception('Error al enviar mensaje a Slack: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Error en EnviarResumenSlackJob: ' . $e->getMessage());
        }
    }
}