<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailBarangKeluar extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id_detail_barang_keluar';

    protected $fillable = [
        'barang_keluar_id',
        'barang_id',
        'barang_stok_id',
        'jumlah',
    ];

    public function barangKeluar(): BelongsTo
    {
        return $this->belongsTo(BarangKeluar::class, 'barang_keluar_id', 'id_barang_keluar');
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
