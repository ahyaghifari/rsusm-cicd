<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dokter extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dokter';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'aktif'               => 'boolean',
        'dapat_konsultasi'    => 'boolean',
        'tersedia_konsultasi' => 'boolean',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the rumah sakit that owns the dokter.
     */
    public function rumahSakit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }

    /**
     * Get the spesialis that owns the dokter.
     */
    public function spesialis(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Spesialis::class, 'spesialis_id');
    }

    /**
     * Nama spesialis untuk ditampilkan — jatuh ke "Dokter Umum" jika dokter
     * tidak memiliki spesialisasi tertentu (spesialis_id kosong).
     */
    public function namaSpesialis(): string
    {
        return $this->spesialis?->nama ?? 'Umum';
    }

    /**
     * Get the jadwal praktek for the dokter.
     */
    public function jadwalPraktek(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JadwalPraktek::class, 'dokter_id');
    }

    /**
     * Get the user account linked to this dokter (jika dokter login & balas chat sendiri).
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the konsultasi chat sessions for the dokter.
     */
    public function sesiKonsultasi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SesiKonsultasi::class, 'dokter_id');
    }
}
