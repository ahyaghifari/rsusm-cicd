<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalHarianPerubahan extends Model
{
    protected $table = 'jadwal_harian_perubahan';

    protected $fillable = [
        'jadwal_harian_id',
        'user_id',
        'jam_mulai',
        'jam_selesai',
        'status_layanan',
        'jam_mulai_asli',
        'jam_selesai_asli',
        'status_layanan_asli',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jam_mulai'           => 'datetime:H:i',
            'jam_selesai'         => 'datetime:H:i',
            'jam_mulai_asli'      => 'datetime:H:i',
            'jam_selesai_asli'    => 'datetime:H:i',
            'status_layanan'      => \App\Enums\StatusLayanan::class,
            'status_layanan_asli' => \App\Enums\StatusLayanan::class,
        ];
    }

    public function jadwalHarian(): BelongsTo
    {
        return $this->belongsTo(JadwalHarian::class, 'jadwal_harian_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
