<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Juego;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

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
        $juego = Juego::with(['user', 'movimientos'])->find($this->juegoId);
        
        $resumen = "Resumen del Juego #{$this->juegoId}*\n";
        $resumen .= " Usuario: {$juego->user->name}\n";
        $resumen .= " Palabra: {$juego->palabra}\n";
        $resumen .= "Estado final: {$juego->status}\n\n";
        
        foreach ($juego->movimientos as $movimiento) {
            $resumen .= "Letra: {$movimiento->letra} | ";
            $resumen .= "Resultado: {$movimiento->resultado} | ";
            $resumen .= "Intentos restantes: {$movimiento->intentos_restantes}\n";
        }

        Http::post(env('https://hooks.slack.com/services/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'), [
            'text' => $resumen
        ]);
    }
}