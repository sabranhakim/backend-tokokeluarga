<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BarangStok extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'barang_id',
        'detail_penerimaan_id',
        'penerimaan_barang_id',
        'batch_number',
        'stok',
        'tgl_kadaluarsa',
        'tgl_masuk',
        'harga_beli',
    ];

    protected $casts = [
        'tgl_kadaluarsa' => 'date',
        'tgl_masuk' => 'date',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function detailPenerimaan(): BelongsTo
    {
        return $this->belongsTo(DetailPenerimaan::class);
    }

    public function penerimaanBarang(): BelongsTo
    {
        return $this->belongsTo(PenerimaanBarang::class);
    }

    public function stockMovements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
