<?php

namespace App\Models;

use App\Enums\Hari;
use App\Enums\StatusLayanan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalLayanan extends Model
{
    use HasFactory;

    protected $table = 'jadwal_layanan';

    protected $fillable = [
        'poliklinik_id',
        'hari',
        'dokter_id',
        'nama_dokter',
        'jam_mulai',
        'jam_selesai',
        'status_layanan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'hari'           => Hari::class,
            'status_layanan' => StatusLayanan::class,
            'jam_mulai'      => 'datetime:H:i',
            'jam_selesai'    => 'datetime:H:i',
        ];
    }

    public function poliklinik(): BelongsTo
    {
        return $this->belongsTo(PoliKlinik::class, 'poliklinik_id');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }

    public function jadwalHarian(): HasMany
    {
        return $this->hasMany(JadwalLayananHarian::class, 'jadwal_layanan_id');
    }
}
