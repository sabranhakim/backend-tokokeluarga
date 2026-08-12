<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturPenjualan extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id_retur_penjualan';

    protected $fillable = [
        'no_retur',
        'user_id',
        'tgl_retur',
        'nama_pelanggan',
        'keterangan',
    ];

    protected $casts = [
        'tgl_retur' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function detailReturPenjualans(): HasMany
    {
        return $this->hasMany(DetailReturPenjualan::class, 'retur_penjualan_id', 'id_retur_penjualan');
    }
}
