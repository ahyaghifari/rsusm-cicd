<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoliKlinik extends Model
{
    use HasFactory, SoftDeletes;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'poliklinik';

    protected $casts = [
        'aktif' => 'boolean',
    ];

    // Allowed fields (Semua kolom kecuali ID)
    protected $fillable = [
        'rumah_sakit_id',
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

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }

    public function jadwalPraktek(): HasMany
    {
        return $this->hasMany(JadwalPraktek::class, 'poliklinik_id');
    }
}