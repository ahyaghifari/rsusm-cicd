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
            'layout' => 'grid_shape', // grid_shape (Banjarbaru) | list_polos (Barabai, belum diimplementasi)
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
                // Header nama poli: kotak statis (bukan zone/drag), styling via warna/gradasi/radius.
                'header_bg_warna'   => '#7c3aed',
                'header_bg_warna2'  => '',        // kosong = solid, isi = gradasi ke warna ini
                'header_radius'     => 8,         // px
                'header_width_pct'   => 70,        // % lebar header nama poli relatif terhadap card
                'header_offset_x'    => 0,         // px, geser kanan(+) / kiri(-) dari tepi card
                'header_padding_left'=> 10,        // px, padding kiri teks di dalam header
                'header_font_weight' => '700',
                'header_font_style'  => 'normal',  // normal | italic
                'card_bg_warna'     => '#ffffff',
                'card_radius'       => 8,
                'card_border_warna' => '#e5e7eb',
                'card_border_width' => 1,
                'card_min_height'   => 0,        // px, 0 = tidak dipakai
                'dokter_valign'     => 'top',    // top | center
                'dokter_row_gap'    => 2,        // px, jarak antar baris dokter
                'card_padding_top'  => 8,        // px, padding tambahan di atas box dokter (di luar overlap header)
                // size_nama_poli / size_nama_dokter / size_jam dihitung otomatis dari lebar card saat render.
                'font_nama_poli'    => ['sumber' => 'google', 'nama' => 'Montserrat'],
                'warna_nama_poli'   => '#FFFFFF',
                'font_isi'          => ['sumber' => 'google', 'nama' => 'Poppins'],
                'warna_nama_dokter' => '#1A1A1A',
                'warna_jam'         => '#1A1A1A',
                'size_nama_poli'    => 8,
                'size_nama_dokter'  => 9,
                'size_jam'          => 9,
                'weight_nama_dokter'=> '600',
                'weight_jam'        => '500',
            ],
            'font_tanggal'    => ['sumber' => 'google', 'nama' => 'Montserrat'],
            'font_keterangan' => ['sumber' => 'google', 'nama' => 'Poppins'],
        ];
    }
}