<?php

namespace App\Jobs;

use App\Models\PenerimaanBarang;
use App\Services\CloudinaryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPenerimaanFotoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        protected string $penerimaanId,
        protected ?string $fotoTempPath = null
    ) {}

    public function handle(CloudinaryService $cloudinaryService): void
    {
        $penerimaan = PenerimaanBarang::find($this->penerimaanId);
        if (! $penerimaan) {
            return;
        }

        if ($this->fotoTempPath && file_exists($this->fotoTempPath)) {
            $fotoUrl = $cloudinaryService->uploadFromPath($this->fotoTempPath);

            if ($fotoUrl) {
                $penerimaan->update(['foto_bon' => $fotoUrl]);
            }

            @unlink($this->fotoTempPath);
        }
    }
}
