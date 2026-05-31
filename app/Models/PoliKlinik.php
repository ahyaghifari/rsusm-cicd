<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PoliKlinik extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'poliklinik';

    protected $casts = [
        'aktif' => 'boolean',
    ];

    // Allowed fields (Semua kolom kecuali ID)
    protected $fillable = [
        'unit_layanan_id',
        'nama',
        'slug',
        'gambar',
        'deskripsi',
        'aktif',
    ];

    /**
     * Route Model Binding menggunakan slug bukan id
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Relasi ke model UnitLayanan
     */
    public function unitLayanan(): BelongsTo
    {
        return $this->belongsTo(UnitLayanan::class, 'unit_layanan_id');
    }

    public function jadwalLayanan(): HasMany
    {
        return $this->hasMany(JadwalLayanan::class, 'poliklinik_id');
    }
}