<?php

namespace App\Filament\PosterLayouts\Layouts;

use App\Filament\PosterLayouts\Contracts\PosterLayout;
use App\Filament\Resources\PosterTemplateResource\Pages\ZoneEditorPage;

class GridShapeLayout implements PosterLayout
{
    public function label(): string
    {
        return 'Grid Shape (Banjarbaru)';
    }

    public function zoneEditorPageClass(): string
    {
        return ZoneEditorPage::class;
    }

    public function templateView(): string
    {
        return 'filament.resources.poster-jadwal-resource.pages.jadwal-template';
    }

    public function defaultConfig(): array
    {
        return [
            'tinggi_hero' => 25,
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
                'gap'   => 16,
                'gap_h' => 16,
                'gap_v' => 16,
                'header_bg_warna'    => '#7c3aed',
                'header_bg_warna2'   => '',
                'header_radius'      => 8,
                'header_width_pct'   => 70,
                'header_offset_x'    => 0,
                'header_padding_left'=> 10,
                'header_font_weight' => '700',
                'header_font_style'  => 'normal',
                'card_bg_warna'     => '#ffffff',
                'card_radius'       => 8,
                'card_border_warna' => '#e5e7eb',
                'card_border_width' => 1,
                'card_min_height'   => 0,
                'dokter_valign'     => 'top',
                'dokter_row_gap'    => 2,
                'card_padding_top'  => 8,
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
