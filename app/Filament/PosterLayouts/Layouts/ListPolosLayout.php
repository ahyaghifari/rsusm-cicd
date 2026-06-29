<?php

namespace App\Filament\PosterLayouts\Layouts;

use App\Filament\PosterLayouts\Contracts\PosterLayout;
use App\Filament\Resources\PosterTemplateResource\Pages\ZoneEditorPageListPolos;

class ListPolosLayout implements PosterLayout
{
    public function label(): string
    {
        return 'List Polos (Barabai)';
    }

    public function zoneEditorPageClass(): string
    {
        return ZoneEditorPageListPolos::class;
    }

    public function templateView(): string
    {
        return 'filament.resources.poster-jadwal-resource.pages.jadwal-template-list-polos';
    }

    public function defaultConfig(): array
    {
        return [
            'zona_tanggal' => [
                'x' => 40, 'y' => 380, 'w' => 1000, 'h' => 70,
                'font' => 'Montserrat', 'size' => 36,
                'warna' => '#1a1a2e', 'align' => 'center',
            ],
            'zona_jadwal' => [
                'x' => 40, 'y' => 480, 'w' => 1000, 'h' => 1400,
            ],
            'grid' => [
                'gap_v'          => 16,
                'gap_h'          => 12,
                'col_nama_persen' => 70,
                'gap_header_dokter'       => 0,
                'dokter_raise'            => 20,
                'padding_dokter_pertama'  => 0,
                'padding_dokter_top'      => 7,
                'padding_dokter_right'    => 8,
                'padding_dokter_bottom'   => 7,
                'padding_dokter_left'     => 14,
                'header_border_warna' => '#dee2e6',
                'header_border_width' => 1,
                'header_bg_warna'  => '#1e3a5f',
                'header_bg_warna2' => '',
                'header_radius'    => 0,
                'card_bg_warna'    => '#f8f9fa',
                'card_radius'      => 8,
                'card_border_warna'=> '#dee2e6',
                'card_border_width'=> 1,
                'font_nama_poli'   => ['sumber' => 'google', 'nama' => 'Montserrat'],
                'warna_nama_poli'         => '#ffffff',
                'outline_poli_width'      => 0,
                'outline_poli_warna'      => '#000000',
                'font_isi'         => ['sumber' => 'google', 'nama' => 'Poppins'],
                'warna_nama_dokter'    => '#1A1A1A',
                'outline_dokter_width' => 0,
                'outline_dokter_warna' => '#000000',
                'warna_jam'            => '#1A1A1A',
                'outline_jam_width'    => 0,
                'outline_jam_warna'    => '#000000',
                'size_nama_poli'   => 30,
                'size_nama_dokter' => 26,
                'size_jam'         => 26,
                'weight_nama_dokter'=> '500',
                'weight_jam'        => '400',
            ],
            'font_tanggal' => ['sumber' => 'google', 'nama' => 'Montserrat'],
        ];
    }
}
