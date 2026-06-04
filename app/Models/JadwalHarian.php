<?php

namespace App\Models;

use App\Enums\StatusLayanan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JadwalHarian extends Model
{
    use HasFactory;

    protected $table = 'jadwal_harian';

    protected $fillable = [
        'poliklinik_id',
        'tanggal',
        'dokter_id',
        'nama_dokter',
        'jam_mulai',
        'jam_selesai',
        'status_layanan',
        'catatan',
        'sumber',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'        => 'date',
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

    public function perubahan(): HasOne
    {
        return $this->hasOne(JadwalHarianPerubahan::class, 'jadwal_harian_id');
    }
}
