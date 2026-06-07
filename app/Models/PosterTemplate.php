<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosterTemplate extends Model
{
    protected $fillable = [
        'rumah_sakit_id',
        'nama',
        'template_png',
        'logo_header',
        'shape_poli',
        'config',
        'is_default',
    ];

    protected $casts = [
        'config'     => 'array',
        'is_default' => 'boolean',
    ];

    // ── Relasi ─────────────────────────────────────────────────────────────────

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class);
    }

    // ── Helper: default config ─────────────────────────────────────────────────

    /**
     * Config JSON default apabila belum ada zone editor yang diatur.
     * Koordinat dalam px relatif terhadap canvas 1080×1920.
     */
    public static function defaultConfig(): array
    {
        return [
            'tinggi_hero' => 25,    // % dari canvas 1920px; 0 = tidak tampil
            'zona_logo' => [
                'x' => 60, 'y' => 60, 'w' => 300, 'h' => 120,
            ],
            'zona_tanggal' => [
                'x' => 80, 'y' => 940, 'w' => 900,
                'font' => 'Montserrat', 'size' => 40,
                'warna' => '#1a1a2e', 'align' => 'left',
                'bg_warna' => 'rgba(255,255,255,0.95)',
            ],
            'zona_keterangan' => [
                'x' => 80, 'y' => 1000, 'w' => 900,
                'font' => 'Poppins', 'size' => 24,
                'warna' => '#F0C040', 'align' => 'left',
                'bg_warna' => '',
                'padding'  => 8,
            ],
            'zona_jadwal' => [
                'x' => 40, 'y' => 1080, 'w' => 1000, 'h' => 780,
            ],
            'grid' => [
                'kolom' => 2,
                'gap' => 16,
                'shape_scale'       => 100,
                'card_bg_warna'     => '#ffffff',
                'card_radius'       => 8,
                'card_border_warna' => '#e5e7eb',
                'card_border_width' => 1,
                'font_nama_poli'    => ['sumber' => 'google', 'nama' => 'Montserrat'],
                'size_nama_poli'    => 15,
                'warna_nama_poli'   => '#FFFFFF',
                'font_isi'          => ['sumber' => 'google', 'nama' => 'Poppins'],
                'size_nama_dokter'  => 13,
                'warna_nama_dokter' => '#1A1A1A',
                'size_jam'          => 12,
                'warna_jam'         => '#555555',
            ],
            'font_tanggal'    => ['sumber' => 'google', 'nama' => 'Montserrat'],
            'font_keterangan' => ['sumber' => 'google', 'nama' => 'Poppins'],
        ];
    }
}