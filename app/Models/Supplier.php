<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_supplier',
        'alamat',
        'no_telp',
    ];

    public function penerimaanBarangs(): HasMany
    {
        return $this->hasMany(PenerimaanBarang::class);
    }
}
