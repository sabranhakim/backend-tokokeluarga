<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BarangStok extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id_barang_stok';

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
        return $this->belongsTo(Barang::class, 'barang_id', 'id_barang');
    }

    public function detailPenerimaan(): BelongsTo
    {
        return $this->belongsTo(DetailPenerimaan::class, 'detail_penerimaan_id', 'id_detail_penerimaan');
    }

    public function penerimaanBarang(): BelongsTo
    {
        return $this->belongsTo(PenerimaanBarang::class, 'penerimaan_barang_id', 'id_penerimaan_barang');
    }

    public function stockMovements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockMovement::class, 'barang_stok_id', 'id_barang_stok');
    }
}
