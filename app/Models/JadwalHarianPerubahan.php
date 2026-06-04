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
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jam_mulai'    => 'datetime:H:i',
            'jam_selesai'  => 'datetime:H:i',
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
