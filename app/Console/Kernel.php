<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
       // $schedule->command('inspire')->hourly();

        // Linha que agenda o seu comando:
        $schedule->command(ProcessarTransacoesFaturamento::class) // Especifica o comando a ser executado
                 ->dailyAt('02:00'); // Define a frequência (diariamente às 2:00 da manhã)
                 // ->everyMinute(); // Use esta linha em vez de dailyAt() para testes rápidos
        
        $schedule->command(ReprocessamentoGeral::class);


    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }


}
