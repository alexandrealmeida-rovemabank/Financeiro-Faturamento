<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage; // Importar
use App\Models\Fatura; // Importar

class FaturaProntaNotification extends Notification
{
    use Queueable;

    protected $fatura;
    protected $fileName;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Fatura $fatura, $fileName)
    {
        $this->fatura = $fatura;
        $this->fileName = $fileName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Vamos enviar para o 'sininho' (banco de dados)
        return ['database']; 
    }

    /**
     * Get the array representation of the notification.
     * (Para o 'sininho'/database)
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'fatura_id' => $this->fatura->id,
            'numero_fatura' => $this->fatura->numero_fatura,
            'message' => "A Fatura Nº {$this->fatura->numero_fatura} está pronta para download.",
            // Gera a URL pública segura para o arquivo
            'url' => Storage::url($this->fileName), 
        ];
    }
}