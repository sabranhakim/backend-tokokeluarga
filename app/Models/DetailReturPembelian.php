<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailReturPembelian extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id_detail_retur_pembelian';

    protected $fillable = [
        'retur_pembelian_id',
        'barang_id',
        'barang_stok_id',
        'jumlah',
    ];

    public function returPembelian(): BelongsTo
    {
        return $this->belongsTo(ReturPembelian::class, 'retur_pembelian_id', 'id_retur_pembelian');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id_barang');
    }

    public function barangStok(): BelongsTo
    {
        return $this->belongsTo(BarangStok::class, 'barang_stok_id', 'id_barang_stok');
    }
}
