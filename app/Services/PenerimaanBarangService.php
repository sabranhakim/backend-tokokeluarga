<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\DetailPenerimaan;
use App\Models\PenerimaanBarang;
use App\Models\User;
use App\Notifications\NewPenerimaanNotification;
use App\Jobs\SendWhatsAppNotificationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PenerimaanBarangService
{
    protected $cloudinaryService;
    protected $whatsappService;

    public function __construct(CloudinaryService $cloudinaryService, WhatsAppService $whatsappService)
    {
        $this->cloudinaryService = $cloudinaryService;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Store a new penerimaan barang along with its details.
     *
     * @param array $data
     * @param mixed $file
     * @return \App\Models\PenerimaanBarang
     */
    public function store(array $data, $file = null)
    {
        return DB::transaction(function () use ($data, $file) {
            $fotoBonUrl = null;
            if ($file) {
                $fotoBonUrl = $this->cloudinaryService->upload($file);
            }

            // 1. Create PenerimaanBarang Header
            $penerimaan = PenerimaanBarang::create([
                'id' => $data['id'] ?? null,
                'no_terima' => $data['no_terima'],
                'supplier_id' => $data['supplier_id'],
                'user_id' => auth()->id(),
                'tgl_terima' => $data['tgl_terima'],
                'foto_bon' => $fotoBonUrl,
                'status_verifikasi' => 'pending', // Default is pending
            ]);

            // 2. Create Details (Stock is NOT updated yet, waiting for verification)
            foreach ($data['items'] as $item) {
                DetailPenerimaan::create([
                    'id' => $item['id'] ?? null,
                    'penerimaan_barang_id' => $penerimaan->id,
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                ]);
            }

            // After all details are saved, reload relationships for the notification
            $penerimaan->load(['supplier', 'user', 'detailPenerimaans.barang']);

            // Send notifications after the transaction is successfully committed
            DB::afterCommit(function () use ($penerimaan) {
                // Internal Database Notification
                $users = User::all();
                Notification::send($users, new NewPenerimaanNotification($penerimaan));

                // Dispatch WhatsApp Job with delay
                $this->dispatchWhatsAppJob($penerimaan);
            });

            return $penerimaan;
        });
    }

    /**
     * Dispatch WhatsApp Notification Job.
     */
    protected function dispatchWhatsAppJob(PenerimaanBarang $penerimaan): void
    {
        $supplier = $penerimaan->supplier;

        if (!$supplier || !$supplier->no_telp) {
            return;
        }

        $itemsList = "";
        foreach ($penerimaan->detailPenerimaans as $detail) {
            $namaBarang = $detail->barang ? $detail->barang->nama_barang : 'Barang tidak diketahui';
            $itemsList .= "- {$namaBarang}: {$detail->jumlah} {$detail->barang->satuan}\n";
        }

        $message = "📦 *PENERIMAAN BARANG BERHASIL*\n\n" .
                   "Halo *{$supplier->nama_supplier}*,\n" .
                   "Barang kiriman Anda telah kami terima di gudang.\n\n" .
                   "Detail:\n" .
                   "📄 No. Terima: *{$penerimaan->no_terima}*\n" .
                   "📅 Tanggal: " . $penerimaan->tgl_terima->format('d-m-Y') . "\n\n" .
                   "Penerima: *{$penerimaan->user->name}*\n\n" .
                   "Daftar Barang:\n" .
                   $itemsList . "\n" .
                   "_Catatan: Foto bukti fisik telah diarsipkan secara digital di sistem kami._\n\n" .
                   "Terima kasih telah menjadi supplier kami.\n" .
                   "---------------------------\n" .
                   "_Pesan Otomatis Grosir Toko Keluarga_";

        // Dispatch Job (dikirim segera setelah transaksi selesai)
        SendWhatsAppNotificationJob::dispatch(
            $supplier->no_telp,
            $message
        )->delay(now()->addSeconds(1));
    }

    /**
     * Verify a penerimaan barang and update stock.
     *
     * @param string $id
     * @return \App\Models\PenerimaanBarang
     */
    public function verify(string $id)
    {
        return DB::transaction(function () use ($id) {
            $penerimaan = PenerimaanBarang::with('detailPenerimaans')->findOrFail($id);

            if ($penerimaan->status_verifikasi === 'verified') {
                throw new \Exception('Penerimaan barang sudah diverifikasi sebelumnya.');
            }

            // 1. Update status
            $penerimaan->update([
                'status_verifikasi' => 'verified'
            ]);

            // 2. Update stock for each item
            foreach ($penerimaan->detailPenerimaans as $detail) {
                $barang = Barang::where('id', $detail->barang_id)->lockForUpdate()->firstOrFail();
                $oldStok = $barang->stok;
                $barang->increment('stok', $detail->jumlah);

                // Log detailed stock movement
                activity()
                    ->performedOn($barang)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'old_stok' => $oldStok,
                        'new_stok' => $barang->stok,
                        'jumlah_masuk' => $detail->jumlah,
                        'no_terima' => $penerimaan->no_terima,
                        'tipe' => 'masuk'
                    ])
                    ->log("Stok barang '{$barang->nama_barang}' bertambah sebanyak {$detail->jumlah} {$barang->satuan} melalui verifikasi penerimaan {$penerimaan->no_terima}");
            }

            return $penerimaan;
        });
    }

    /**
     * Get all penerimaan barang with relationships.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return PenerimaanBarang::with(['supplier', 'user', 'detailPenerimaans.barang'])->latest()->get();
    }

    /**
     * Get a single penerimaan barang with relationships.
     *
     * @param int $id
     * @return \App\Models\PenerimaanBarang
     */
    public function getById($id)
    {
        return PenerimaanBarang::with(['supplier', 'user', 'detailPenerimaans.barang'])->findOrFail($id);
    }
}
