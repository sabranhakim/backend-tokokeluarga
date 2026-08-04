<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturPembelian extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'no_retur',
        'supplier_id',
        'user_id',
        'tgl_retur',
        'keterangan',
    ];

    protected $casts = [
        'tgl_retur' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed()->withDefault([
            'nama_supplier' => 'Supplier telah dihapus permanen',
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detailReturPembelians(): HasMany
    {
        return $this->hasMany(DetailReturPembelian::class);
    }
}
