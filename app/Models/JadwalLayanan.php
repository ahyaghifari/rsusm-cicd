<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalLayanan extends Model
{
    // Deklarasi nama tabel secara eksplisit
    protected $table = 'jadwal_layanan';

    // Semua kolom dapat diisi kecuali ID
    protected $fillable = [
        'poliklinik_id',
        'tanggal',
        'dokter_id',
        'nama_dokter',
        'jam_mulai',
        'jam_selesai',
        'status_layanan',
        'catatan',
    ];

    /**
     * Mengatur casting tipe data untuk kolom tanggal dan waktu
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam_mulai' => 'datetime:H:i',
            'jam_selesai' => 'datetime:H:i',
        ];
    }

    /**
     * Relasi ke model PoliKlinik
     */
    public function poliklinik(): BelongsTo
    {
        return $this->belongsTo(PoliKlinik::class, 'poliklinik_id');
    }

    /**
     * Relasi ke model Dokter (Pastikan Anda sudah memiliki Model Dokter)
     */
    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }
}