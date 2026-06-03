<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FasilitasPendukung extends Model
{
    // Definisikan nama tabel secara eksplisit
    protected $table = 'fasilitas_pendukung';

    // Allowed fields semua kolom kecuali kolom id
    protected $fillable = [
        'sort_order',
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
