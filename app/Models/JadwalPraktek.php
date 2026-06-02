<?php

namespace App\Models;

use App\Enums\Hari;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPraktek extends Model
{
    use HasFactory;

    protected $table = 'jadwal_praktek';

    protected $fillable = [
        'poliklinik_id',
        'hari',
        'dokter_id',
        'nama_dokter',
        'waktu_mulai',
        'waktu_selesai',
        'sesuai_perjanjian',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'hari'              => Hari::class,
            'waktu_mulai'       => 'datetime:H:i',
            'waktu_selesai'     => 'datetime:H:i',
            'sesuai_perjanjian' => 'boolean',
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
}
