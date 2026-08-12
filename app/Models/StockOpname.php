<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpname extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id_stock_opname';

    protected $fillable = [
        'no_opname',
        'user_id',
        'tgl_opname',
        'keterangan',
        'status',
        'total_selisih',
    ];

    protected $casts = [
        'tgl_opname' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function detailStockOpnames(): HasMany
    {
        return $this->hasMany(DetailStockOpname::class, 'stock_opname_id', 'id_stock_opname');
    }
}
