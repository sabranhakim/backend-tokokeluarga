<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Barang extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $primaryKey = 'id_barang';

    protected $fillable = [
        'is_active',
        'kode_barang',
        'nama_barang',
        'kategori_id',
        'supplier_id',
        'satuan',
        'isi',
        'harga_beli',
        'harga_jual',
        'stok',
        'stok_minimal',
    ];

    protected static function booted()
    {
        static::addGlobalScope('active', function ($builder) {
            $builder->where('is_active', true);
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id', 'id_kategori');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id_supplier');
    }

    public function detailPenerimaans(): HasMany
    {
        return $this->hasMany(DetailPenerimaan::class, 'barang_id', 'id_barang');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'barang_id', 'id_barang');
    }

    public function barangStoks(): HasMany
    {
        return $this->hasMany(BarangStok::class, 'barang_id', 'id_barang');
    }

    public function getStokTotalAttribute(): int
    {
        return $this->barangStoks()->sum('stok');
    }
}
