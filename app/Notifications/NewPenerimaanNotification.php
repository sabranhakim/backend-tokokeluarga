<?php

namespace App\Notifications;

use App\Models\PenerimaanBarang;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPenerimaanNotification extends Notification
{
    use Queueable;

    protected $penerimaan;

    /**
     * Create a new notification instance.
     */
    public function __construct(PenerimaanBarang $penerimaan)
    {
        $this->penerimaan = $penerimaan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'penerimaan_id' => $this->penerimaan->id,
            'no_terima' => $this->penerimaan->no_terima,
            'supplier_nama' => $this->penerimaan->supplier->nama_supplier,
            'petugas_nama' => $this->penerimaan->user->name,
            'message' => "Penerimaan barang baru #{$this->penerimaan->no_terima} dari {$this->penerimaan->supplier->nama_supplier} telah masuk.",
            'type' => 'new_penerimaan'
        ];
    }
}
