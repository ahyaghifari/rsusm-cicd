<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoliKlinik extends Model
{
    use HasFactory, SoftDeletes;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'poliklinik';

    protected $casts = [
        'aktif'              => 'boolean',
        'jadwal_tidak_tetap' => 'boolean',
        'prioritas_poster'   => 'boolean',
    ];

    // Allowed fields (Semua kolom kecuali ID)
    protected $fillable = [
        'rumah_sakit_id',
        'nama',
        'slug',
        'gambar',
        'deskripsi',
        'aktif',
        'jadwal_tidak_tetap',
        'sort_order',
        'prioritas_poster',
        'posisi_prioritas',
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

    public function jadwalHarian(): HasMany
    {
        return $this->hasMany(JadwalHarian::class, 'poliklinik_id');
    }

    public function dokter(): BelongsToMany
    {
        return $this->belongsToMany(Dokter::class, 'poliklinik_dokter', 'poliklinik_id', 'dokter_id');
    }
}