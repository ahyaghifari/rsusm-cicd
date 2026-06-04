<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenunjangMedis extends Model
{
    protected $table = 'penunjang_medis';

    protected $fillable = [
        'sort_order',
        'rumah_sakit_id',
        'nama',
        'gambar',
        'deskripsi',
        'aktif',
    ];

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }
}

