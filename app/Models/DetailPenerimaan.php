<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailPenerimaan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'penerimaan_barang_id',
        'barang_id',
        'jumlah',
        'batch_number',
        'tgl_kadaluarsa',
    ];

    protected $casts = [
        'tgl_kadaluarsa' => 'date',
    ];

    public function penerimaanBarang(): BelongsTo
    {
        return $this->belongsTo(PenerimaanBarang::class);
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function barangStoks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BarangStok::class);
    }
}
