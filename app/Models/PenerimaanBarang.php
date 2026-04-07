<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaanBarang extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_terima',
        'supplier_id',
        'user_id',
        'tgl_terima',
        'foto_bon',
        'status_verifikasi',
    ];

    protected $casts = [
        'tgl_terima' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detailPenerimaans(): HasMany
    {
        return $this->hasMany(DetailPenerimaan::class);
    }
}
