<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RumahSakit;

class RumahSakitSeeder extends Seeder
{
    public function run(): void
    {
        RumahSakit::create([
            'nama'                   => 'RSU Syifa Medika Banjarbaru',
            'slug'                   => 'banjarbaru',
            'lokasi'                 => 'Banjarbaru',
            'alamat'                 => 'Jl. RO Ulin No.93, Loktabat Selatan, Kec. Banjarbaru Selatan, Kota Banjar Baru, Kalimantan Selatan 70712',
            'no_emergency'           => '0811 504 2424',
            'no_hotline'             => '0511 5910 889',
            'gambar'                 => null,
            'logo'                   => null,
            'aktif'                  => true,
            'link_pendaftaran_online' => null,
            'lokasi_google_map'      => null,
            'tentang_kami'           => '<p>RSU Syifa Medika Banjarbaru hadir untuk masyarakat yang ingin mendapatkan pelayanan kesehatan yang berkualitas. RSU Syifa Medika Banjarbaru merupakan pelayanan kesehatan yang jujur dalam pelayanan dan selalu memberikan kemudahan karena didukung oleh staff medis yang profesional, bersertifikasi, ahli di bidangnya serta didukung oleh peralatan yang mutakhir dan terkini sesuai dengan moto kami yaitu <strong>Pelayanan yang profesional dan terpercaya</strong>.</p>',
            'gambar_tentang'         => null,
        ]);

        RumahSakit::create([
            'nama'                   => 'RSU Syifa Medika Barabai',
            'slug'                   => 'barabai',
            'lokasi'                 => 'Barabai',
            'alamat'                 => 'Jl Lingkar Walangsi Kapar KM. 5.2, Barabai, Kalimantan Selatan, Indonesia',
            'no_emergency'           => '-',
            'no_hotline'             => '-',
            'gambar'                 => null,
            'logo'                   => null,
            'aktif'                  => true,
            'link_pendaftaran_online' => null,
            'lokasi_google_map'      => null,
            'tentang_kami'           => '<p>RSU Syifa Medika Barabai hadir sebagai pilihan layanan kesehatan terpercaya bagi masyarakat Kabupaten Hulu Sungai Tengah dan sekitarnya. Didukung oleh tenaga medis profesional dan fasilitas modern, kami berkomitmen untuk memberikan pelayanan kesehatan yang berkualitas, jujur, dan terjangkau bagi seluruh lapisan masyarakat.</p>',
            'gambar_tentang'         => null,
        ]);
    }
}
