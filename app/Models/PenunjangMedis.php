<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenunjangMedis extends Model
{
    protected $table = 'penunjang_medis';

    protected $fillable = [
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
