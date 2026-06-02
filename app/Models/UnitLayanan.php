<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitLayanan extends Model
{
    use HasFactory;

    // Deklarasi nama tabel database secara eksplisit
    protected $table = 'unit_layanan';

    // Allowed fields semua kolom kecuali kolom id
    protected $fillable = [
        'rumah_sakit_id',
        'nama',
        'deskripsi',
        'gambar',
        'aktif',
        'warna',
    ];

    // Nilai warna hex mentah, fallback ke tertiary
    public function warnaHex(): string
    {
        return $this->warna ?? '#4d51b2';
    }

    // Inline style background-color
    public function warnaStyle(): string
    {
        return "background-color: {$this->warnaHex()};";
    }

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }

    public function poliklinik(): HasMany
    {
        return $this->hasMany(PoliKlinik::class, 'unit_layanan_id');
    }
}