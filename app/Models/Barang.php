<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori_id',
        'satuan',
        'harga_beli',
        'harga_jual',
        'stok',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function detailPenerimaans(): HasMany
    {
        return $this->hasMany(DetailPenerimaan::class);
    }

    public function detailPenjualans(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class);
    }
}
