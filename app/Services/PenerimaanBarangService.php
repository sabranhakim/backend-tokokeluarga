<?php

namespace App\Services;

use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\Barang;
use App\Models\BarangStok;
use App\Models\DetailPenerimaan;
use App\Models\PenerimaanBarang;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\NewPenerimaanNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PenerimaanBarangService
{
    protected $cloudinaryService;

    public function __construct(
        CloudinaryService $cloudinaryService,
    ) {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Store a new penerimaan barang along with its details.
     *
     * @param  mixed  $file
     * @return PenerimaanBarang
     */
    public function store(array $data, $file = null)
    {
        return DB::transaction(function () use ($data, $file) {
            $fotoBonUrl = null;
            if ($file) {
                $fotoBonUrl = $this->cloudinaryService->upload($file);
            }

            // Gunakan no_terima dari klien (untuk idempotency/dedupe), fallback generate di server
            $noTerima = !empty($data["no_terima"]) ? $data["no_terima"] : $this->generateNoTerima();

            // 1. Create PenerimaanBarang Header
            $penerimaan = PenerimaanBarang::create([
                "no_terima" => $noTerima,
                "supplier_id" => $data["supplier_id"],
                "user_id" => auth()->id(),
                "tgl_terima" => $data["tgl_terima"],
                "foto_bon" => $fotoBonUrl,
                "status_verifikasi" => "pending", // Default is pending
            ]);

            // 2. Create Details (Stock is NOT updated yet, waiting for verification)
            foreach ($data["items"] as $item) {
                DetailPenerimaan::create([
                    "penerimaan_barang_id" => $penerimaan->getKey(),
                    "barang_id" => $item["barang_id"],
                    "jumlah" => $item["jumlah"],
                    "batch_number" => !empty($item["batch_number"]) ? $item["batch_number"] : null,
                    "tgl_kadaluarsa" => !empty($item["tgl_kadaluarsa"]) ? $item["tgl_kadaluarsa"] : null,
                ]);
            }

            // After all details are saved, reload relationships for the notification
            $penerimaan->load(["supplier", "user", "detailPenerimaans.barang"]);

            // Send notifications after the transaction is successfully committed
            DB::afterCommit(function () use ($penerimaan) {
                // Internal Database Notification
                $users = User::all();
                Notification::send(
                    $users,
                    new NewPenerimaanNotification($penerimaan),
                );

                // WhatsApp Notification to Supplier (sent right after input)
                $this->dispatchWhatsAppNotification($penerimaan);
            });

            return $penerimaan;
        });
    }

    /**
     * Update a pending (unverified) penerimaan barang along with its details.
     * Verified penerimaan cannot be edited.
     *
     * @param  mixed  $file
     * @return PenerimaanBarang
     */
    public function update(string $id, array $data, $file = null)
    {
        return DB::transaction(function () use ($id, $data, $file) {
            $penerimaan = PenerimaanBarang::with("detailPenerimaans")->findOrFail($id);

            if ($penerimaan->status_verifikasi !== "pending") {
                throw new \Exception(
                    "Penerimaan barang sudah diverifikasi dan tidak dapat diedit.",
                );
            }

            $fotoBonUrl = $penerimaan->foto_bon;
            if ($file) {
                $fotoBonUrl = $this->cloudinaryService->upload($file);
            }

            // 1. Update PenerimaanBarang Header
            $penerimaan->update([
                "supplier_id" => $data["supplier_id"],
                "tgl_terima" => $data["tgl_terima"],
                "foto_bon" => $fotoBonUrl,
            ]);

            // 2. Replace details (Stock is NOT updated yet, waiting for verification)
            DetailPenerimaan::where(
                "penerimaan_barang_id",
                $penerimaan->getKey(),
            )->delete();
            foreach ($data["items"] as $item) {
                DetailPenerimaan::create([
                    "penerimaan_barang_id" => $penerimaan->getKey(),
                    "barang_id" => $item["barang_id"],
                    "jumlah" => $item["jumlah"],
                    "batch_number" => !empty($item["batch_number"]) ? $item["batch_number"] : null,
                    "tgl_kadaluarsa" => !empty($item["tgl_kadaluarsa"]) ? $item["tgl_kadaluarsa"] : null,
                ]);
            }

            // No notification is re-sent here. Notification stays after the
            // initial input (store), not after verification.

            return $penerimaan->fresh(["supplier", "user", "detailPenerimaans.barang"]);
        });
    }

    /**
     * Verify a penerimaan barang and update stock.
     *
     * @return PenerimaanBarang
     */
    public function verify(string $id, ?string $catatanVerifikasi = null)
    {
        return DB::transaction(function () use ($id, $catatanVerifikasi) {
            $penerimaan = PenerimaanBarang::with([
                "supplier",
                "detailPenerimaans.barang",
            ])->findOrFail($id);

            if ($penerimaan->status_verifikasi === "verified") {
                throw new \Exception(
                    "Penerimaan barang sudah diverifikasi sebelumnya.",
                );
            }

            // 1. Update status
            $penerimaan->update([
                "status_verifikasi" => "verified",
                "catatan_verifikasi" => $catatanVerifikasi,
            ]);

            // 2. Create batch stock entries and update stock for each item
            foreach ($penerimaan->detailPenerimaans as $detail) {
                $barang = Barang::where("id_barang", $detail->barang_id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $oldStokTotal = $barang->stok;

                // Create batch stock entry (BarangStok)
                $barangStok = BarangStok::create([
                    "barang_id" => $barang->getKey(),
                    "detail_penerimaan_id" => $detail->getKey(),
                    "penerimaan_barang_id" => $penerimaan->getKey(),
                    "batch_number" => $detail->batch_number,
                    "stok" => $detail->jumlah,
                    "tgl_kadaluarsa" => $detail->tgl_kadaluarsa,
                    "tgl_masuk" => $penerimaan->tgl_terima,
                    "harga_beli" => $barang->harga_beli,
                ]);

                // Update cached total stock on barang
                $barang->increment("stok", $detail->jumlah);
                $newStokTotal = $barang->stok;

                // Record Stock Movement for Audit
                StockMovement::create([
                    "barang_id" => $barang->getKey(),
                    "barang_stok_id" => $barangStok->getKey(),
                    "user_id" => auth()->id(),
                    "type" => "in",
                    "quantity" => $detail->jumlah,
                    "before_quantity" => $oldStokTotal,
                    "after_quantity" => $newStokTotal,
                    "reason" =>
                        \Illuminate\Support\Str::limit(
                            "Penerimaan Barang #{$penerimaan->no_terima}" .
                            ($catatanVerifikasi ? " ({$catatanVerifikasi})" : ""),
                            50,
                            ''
                        ),
                    "reference_id" => $penerimaan->getKey(),
                    "reference_type" => PenerimaanBarang::class,
                ]);

                // Log detailed stock movement (Activity Log)
                $batchLabel = $detail->batch_number ?? "-";
                activity()
                    ->performedOn($barang)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        "old_stok" => $oldStokTotal,
                        "new_stok" => $newStokTotal,
                        "jumlah_masuk" => $detail->jumlah,
                        "batch_number" => $detail->batch_number,
                        "tgl_kadaluarsa" => $detail->tgl_kadaluarsa,
                        "no_terima" => $penerimaan->no_terima,
                        "tipe" => "masuk",
                    ])
                    ->log(
                        "Stok barang '{$barang->nama_barang}' bertambah sebanyak {$detail->jumlah} {$barang->satuan} (Batch: {$batchLabel}) melalui verifikasi penerimaan {$penerimaan->no_terima}",
                    );
            }

            return $penerimaan;
        });
    }

    /**
     * Dispatch WhatsApp Notification Job to supplier.
     */
    protected function dispatchWhatsAppNotification(
        PenerimaanBarang $penerimaan,
    ): void {
        $supplier = $penerimaan->supplier;

        if (!$supplier || !$supplier->no_telp) {
            return;
        }

        $itemsList = "";
        foreach ($penerimaan->detailPenerimaans as $detail) {
            $namaBarang = $detail->barang
                ? $detail->barang->nama_barang
                : "Barang tidak diketahui";
            $itemsList .= "- {$namaBarang}: {$detail->jumlah} {$detail->barang->satuan}\n";
        }

        $catatanText = "";
        if ($penerimaan->catatan_verifikasi) {
            $catatanText = "📝 Catatan Verifikasi: *{$penerimaan->catatan_verifikasi}*\n\n";
        }

        $message =
            "📦 *PENERIMAAN BARANG DICATAT*\n\n" .
            "Halo *{$supplier->nama_supplier}*,\n" .
            "Kami menginformasikan bahwa pengiriman barang Anda telah diterima.\n\n" .
            "Detail:\n" .
            "📄 No. Terima: *{$penerimaan->no_terima}*\n" .
            "📅 Tgl Masuk: " .
            now()->format("d-m-Y H:i") .
            "\n\n" .
            $catatanText .
            "Daftar Barang:\n" .
            $itemsList .
            "\n" .
            "Terima kasih atas kerja samanya.\n" .
            "---------------------------\n" .
            "_Pesan Otomatis Grosir Toko Keluarga_";

        SendWhatsAppNotificationJob::dispatch($supplier->no_telp, $message);
    }

    /**
     * Get all penerimaan barang with relationships.
     *
     * @return Collection
     */
    public function getAll()
    {
        return PenerimaanBarang::with([
            "supplier",
            "user",
            "detailPenerimaans.barang",
        ])
            ->latest()
            ->get();
    }

    /**
     * Get a single penerimaan barang with relationships.
     *
     * @param  int  $id
     * @return PenerimaanBarang
     */
    public function getById($id)
    {
        return PenerimaanBarang::with([
            "supplier",
            "user",
            "detailPenerimaans.barang",
        ])->findOrFail($id);
    }

    /**
     * Generate a unique receipt number.
     *
     * @return string
     */
    public function generateNoTerima(): string
    {
        do {
            $noTerima =
                "TRM-" . date("Ymd") . strtoupper(bin2hex(random_bytes(3)));
        } while (PenerimaanBarang::where("no_terima", $noTerima)->exists());

        return $noTerima;
    }
}
