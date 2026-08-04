<?php

namespace App\Filament\PosterLayouts\Layouts;

use App\Filament\PosterLayouts\Contracts\PosterLayout;
use App\Filament\Resources\PosterTemplateResource\Pages\ZoneEditorPagePerubahanJadwal;

class PerubahanJadwalLayout implements PosterLayout
{
    public function label(): string
    {
        return 'Perubahan Jadwal';
    }

    public function zoneEditorPageClass(): string
    {
        return ZoneEditorPagePerubahanJadwal::class;
    }

    public function templateView(): string
    {
        return 'filament.resources.poster-jadwal-resource.pages.jadwal-perubahan-template';
    }

    public function quickConfigFields(): array
    {
        return [
            ['key' => 'gap_v',            'label' => 'Jarak antar kartu',        'quick_setting' => true],
            ['key' => 'size_nama_klinik', 'label' => 'Ukuran font nama klinik',  'quick_setting' => true],
            ['key' => 'size_nama_dokter', 'label' => 'Ukuran font nama dokter',  'quick_setting' => true],
            ['key' => 'size_jam',         'label' => 'Ukuran font jam',          'quick_setting' => true],
        ];
    }

    public function defaultConfig(): array
    {
        return [
            'zona_judul' => [
                'x' => 60, 'y' => 140, 'w' => 960, 'h' => 160,
            ],
            'zona_tanggal' => [
                'x' => 60, 'y' => 300, 'w' => 400, 'h' => 60,
                'font' => 'Montserrat', 'size' => 30,
                'warna' => '#ffffff', 'bg_warna' => '#c0392b', 'align' => 'center',
            ],
            'zona_konten' => [
                'x' => 60, 'y' => 400, 'w' => 960, 'h' => 1300,
            ],
            'grid' => [
                'gap_v'                  => 24,
                'judul_warna'            => '#c0392b',
                'judul_size'             => 56,
                'label_executive'        => 'Klinik Executive',
                'label_reguler'          => 'Poliklinik Reguler',
                'section_title_warna'    => '#c0392b',
                'section_title_size'     => 32,
                'card_bg_warna'          => '#ffffff',
                'card_border_warna'      => '#c0392b',
                'card_border_width'      => 2,
                'card_radius'            => 12,
                'font_nama_klinik'       => ['sumber' => 'google', 'nama' => 'Montserrat'],
                'font_isi'               => ['sumber' => 'google', 'nama' => 'Poppins'],
                'warna_nama_klinik'      => '#1a1a2e',
                'warna_sub_klinik'       => '#7c3aed',
                'warna_nama_dokter'      => '#1a1a2e',
                'size_nama_klinik'       => 26,
                'size_nama_dokter'       => 24,
                'size_jam'               => 20,
                'pill_awal_bg_warna'     => '#f3f4f6',
                'pill_awal_warna'        => '#1a1a2e',
                'pill_baru_bg_warna'     => '#dcfce7',
                'pill_baru_warna'        => '#166534',
                'badge_libur_bg_warna'   => '#dc2626',
                'badge_libur_warna'      => '#ffffff',
            ],
            'font_tanggal' => ['sumber' => 'google', 'nama' => 'Montserrat'],
        ];
    }
}
