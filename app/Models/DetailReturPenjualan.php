<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailReturPenjualan extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id_detail_retur_penjualan';

    protected $fillable = [
        'retur_penjualan_id',
        'barang_id',
        'jumlah',
    ];

    public function returPenjualan(): BelongsTo
    {
        return $this->belongsTo(ReturPenjualan::class, 'retur_penjualan_id', 'id_retur_penjualan');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id_barang');
    }
}
