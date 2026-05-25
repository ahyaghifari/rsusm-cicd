<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LayananUnggulan extends Model
{
    // Definisikan nama tabel secara eksplisit karena menggunakan snake_case jamak
    protected $table = 'layanan_unggulan';

    // Allowed fields (Semua kolom kecuali id)
    protected $fillable = [
        'rumah_sakit_id',
        'nama',
        'gambar',
        'deskripsi',
        'aktif',
    ];

    /**
     * Relasi ke model RumahSakit
     */
    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }
}