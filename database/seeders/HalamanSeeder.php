<?php

namespace Database\Seeders;

use App\Models\Halaman;
use App\Models\RumahSakit;
use Illuminate\Database\Seeder;

class HalamanSeeder extends Seeder
{
    public function run(): void
    {
        $rsBanjarbaru = RumahSakit::where('slug', 'banjarbaru')->first();
        $rsBarabai    = RumahSakit::where('slug', 'barabai')->first();

        $data = [];

        if ($rsBanjarbaru) {
            $data[] = [
                'rumah_sakit_id' => $rsBanjarbaru->id,
                'slug'           => 'profil-perusahaan',
                'judul'          => 'Profil Perusahaan',
                'konten'         => '<h2>Tentang RSU Syifa Medika Banjarbaru</h2><p>RSU Syifa Medika Banjarbaru hadir sebagai rumah sakit umum yang berkomitmen memberikan pelayanan kesehatan terbaik bagi masyarakat Kalimantan Selatan.</p><h3>Akreditasi</h3><p>RSU Syifa Medika telah terakreditasi KARS Paripurna dan terus berupaya meningkatkan mutu layanan secara berkelanjutan.</p>',
                'aktif'          => true,
            ];
            $data[] = [
                'rumah_sakit_id' => $rsBanjarbaru->id,
                'slug'           => 'visi-misi',
                'judul'          => 'Visi & Misi',
                'konten'         => '<h2>Visi</h2><p>Menjadi rumah sakit pilihan utama masyarakat Kalimantan Selatan yang memberikan pelayanan kesehatan profesional, terpercaya, dan berkeadilan.</p><h2>Misi</h2><ul><li>Memberikan pelayanan kesehatan yang profesional dan terpercaya.</li><li>Mengutamakan keselamatan dan kenyamanan pasien.</li><li>Mengembangkan SDM yang kompeten dan berintegritas.</li><li>Meningkatkan aksesibilitas layanan kesehatan bagi seluruh lapisan masyarakat.</li></ul><h2>Nilai</h2><ul><li><strong>Jujur</strong> — Transparansi dalam setiap tindakan medis dan administratif.</li><li><strong>Ikhlas</strong> — Melayani dengan sepenuh hati.</li><li><strong>Profesional</strong> — Berstandar tinggi dalam kompetensi dan etika.</li><li><strong>Terpercaya</strong> — Menjadi mitra kesehatan yang dapat diandalkan.</li></ul>',
                'aktif'          => true,
            ];
        }

        if ($rsBarabai) {
            $data[] = [
                'rumah_sakit_id' => $rsBarabai->id,
                'slug'           => 'profil-perusahaan',
                'judul'          => 'Profil Perusahaan',
                'konten'         => '<h2>Tentang RSU Syifa Medika Barabai</h2><p>RSU Syifa Medika Barabai melayani masyarakat Hulu Sungai Tengah dan sekitarnya dengan fasilitas kesehatan modern dan tenaga medis berpengalaman.</p>',
                'aktif'          => true,
            ];
            $data[] = [
                'rumah_sakit_id' => $rsBarabai->id,
                'slug'           => 'visi-misi',
                'judul'          => 'Visi & Misi',
                'konten'         => '<h2>Visi</h2><p>Menjadi rumah sakit unggulan di wilayah Hulu Sungai yang memberikan pelayanan kesehatan berkualitas dan terjangkau.</p><h2>Misi</h2><ul><li>Menyediakan pelayanan medis berstandar nasional.</li><li>Meningkatkan kesejahteraan masyarakat melalui pelayanan kesehatan preventif dan kuratif.</li><li>Membangun tim medis yang profesional dan berdedikasi.</li></ul>',
                'aktif'          => true,
            ];
        }

        foreach ($data as $item) {
            Halaman::firstOrCreate(
                ['rumah_sakit_id' => $item['rumah_sakit_id'], 'slug' => $item['slug']],
                $item
            );
        }
    }
}
