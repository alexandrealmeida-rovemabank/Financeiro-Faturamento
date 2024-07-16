<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProcessosController extends Controller
{
    public function index()
    {
        return view('Processos.index');
    }

    public function progresso()
    {
        // Ler o arquivo de log para obter as últimas entradas
        $logFile = storage_path('logs/atualizacao_logistica.log');
        if (file_exists($logFile)) {
            $log = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $progresso = array_slice($log,1000); // Retorna as últimas 10 linhas do log
        } else {
            $progresso = [];
        }
        
        $progresso = array_reverse($progresso);

        return response()->json($progresso);
    }
}
