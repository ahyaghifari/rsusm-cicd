<?php

namespace App\Models;

use App\Enums\StatusSesiKonsultasi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesiKonsultasi extends Model
{
    use HasFactory;

    protected $table = 'sesi_konsultasi';

    protected $fillable = [
        'rumah_sakit_id',
        'dokter_id',
        'token',
        'nama_pasien',
        'kontak_pasien',
        'status',
        'durasi_menit',
        'dibalas_oleh',
        'mulai_at',
        'berakhir_at',
    ];

    protected $casts = [
        'status'      => StatusSesiKonsultasi::class,
        'mulai_at'    => 'datetime',
        'berakhir_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }

    public function pembalas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibalas_oleh');
    }

    public function pesan(): HasMany
    {
        return $this->hasMany(KonsultasiPesan::class, 'sesi_id');
    }

    public function sisaDetik(): int
    {
        if (! $this->berakhir_at) {
            return 0;
        }

        return max(0, now()->diffInSeconds($this->berakhir_at, false));
    }
}
