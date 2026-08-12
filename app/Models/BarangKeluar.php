<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BarangKeluar extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id_barang_keluar';

    protected $fillable = [
        'no_keluar',
        'user_id',
        'tgl_keluar',
        'jenis_keluar',
        'keterangan',
    ];

    protected $casts = [
        'tgl_keluar' => 'date',
    ];

    public const JENIS_KELUAR = [
        'penjualan' => 'Penjualan',
        'kerusakan' => 'Kerusakan',
        'kadaluarsa' => 'Kadaluarsa',
        'pemakaian_internal' => 'Pemakaian Internal',
    ];

    public function getJenisKeluarLabelAttribute(): string
    {
        return self::JENIS_KELUAR[$this->jenis_keluar] ?? $this->jenis_keluar;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function detailBarangKeluars(): HasMany
    {
        return $this->hasMany(DetailBarangKeluar::class, 'barang_keluar_id', 'id_barang_keluar');
    }
}
