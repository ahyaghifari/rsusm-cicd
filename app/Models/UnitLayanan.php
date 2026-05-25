<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitLayanan extends Model
{
    // Deklarasi nama tabel database secara eksplisit
    protected $table = 'unit_layanan';

    // Allowed fields semua kolom kecuali kolom id
    protected $fillable = [
        'rumah_sakit_id',
        'nama',
        'deskripsi',
        'gambar',
        'aktif',
    ];

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }

    public function poliklinik(): HasMany
    {
        return $this->hasMany(PoliKlinik::class, 'unit_layanan_id');
    }
}