<?php

namespace App\Notifications;

use App\Models\Barang;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    protected $barang;

    /**
     * Create a new notification instance.
     */
    public function __construct(Barang $barang)
    {
        $this->barang = $barang;
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
            'barang_id' => $this->barang->id,
            'nama_barang' => $this->barang->nama_barang,
            'kode_barang' => $this->barang->kode_barang,
            'stok' => $this->barang->stok,
            'stok_minimal' => $this->barang->stok_minimal,
            'message' => "Stok barang {$this->barang->nama_barang} ({$this->barang->kode_barang}) menipis! Sisa: {$this->barang->stok}",
            'type' => 'low_stock'
        ];
    }
}
