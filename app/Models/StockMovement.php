<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_stock_movement';

    protected $fillable = [
        'barang_id',
        'barang_stok_id',
        'user_id',
        'type',
        'quantity',
        'before_quantity',
        'after_quantity',
        'reason',
        'reference_id',
        'reference_type',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id_barang');
    }

    public function barangStok(): BelongsTo
    {
        return $this->belongsTo(BarangStok::class, 'barang_stok_id', 'id_barang_stok');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
