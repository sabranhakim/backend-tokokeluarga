<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailPenerimaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id_detail_penerimaan';

    protected $fillable = [
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
        return $this->belongsTo(PenerimaanBarang::class, 'penerimaan_barang_id', 'id_penerimaan_barang');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id_barang');
    }

    public function barangStoks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BarangStok::class, 'detail_penerimaan_id', 'id_detail_penerimaan');
    }
}
