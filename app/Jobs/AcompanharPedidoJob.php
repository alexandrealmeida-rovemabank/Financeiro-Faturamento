<?php

namespace App\Jobs;

use App\Models\Logistica_reversa;
use App\Services\LogisticaService; // Supondo que a lógica esteja em um serviço
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AcompanharPedidoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $logisticaService;

    public function __construct(LogisticaService $logisticaService)
    {
        $this->logisticaService = $logisticaService;
    }

    public function handle()
    {
        $this->logisticaService->acompanharPedido();
    }
}

