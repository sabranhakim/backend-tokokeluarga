<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PenerimaanBarang extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
