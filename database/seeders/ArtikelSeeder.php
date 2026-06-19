<?php

namespace Database\Seeders;

use App\Models\Artikel;
use App\Models\KategoriArtikel;
use App\Models\RumahSakit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArtikelSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriData = [
            [
                'nama' => 'Tips Kesehatan',
                'artikel' => [
                    [
                        'judul' => '5 Cara Menjaga Imunitas Tubuh di Musim Pancaroba',
                        'ringkasan' => 'Perubahan cuaca yang tidak menentu rentan membuat daya tahan tubuh menurun. Simak tips menjaga imunitas agar tetap fit.',
                        'konten' => '<p>Musim pancaroba sering diiringi dengan meningkatnya kasus flu, batuk, dan demam. Hal ini terjadi karena tubuh perlu beradaptasi dengan perubahan suhu dan kelembapan udara yang cepat.</p><p>Beberapa langkah sederhana yang dapat dilakukan untuk menjaga imunitas antara lain: mengonsumsi makanan bergizi seimbang, cukup istirahat 7-8 jam per hari, rutin berolahraga ringan, menjaga kebersihan tangan, serta minum air putih yang cukup setiap hari.</p><p>Jika gejala sakit berlanjut lebih dari 3 hari, segera periksakan diri ke dokter agar mendapatkan penanganan yang tepat.</p>',
                    ],
                    [
                        'judul' => 'Pentingnya Sarapan Sehat Sebelum Beraktivitas',
                        'ringkasan' => 'Sarapan bukan sekadar mengisi perut, tapi juga menjaga konsentrasi dan energi sepanjang hari.',
                        'konten' => '<p>Banyak orang melewatkan sarapan karena terburu-buru, padahal sarapan memiliki peran penting dalam menjaga kadar gula darah dan energi tubuh sepanjang pagi hingga siang hari.</p><p>Pilihlah menu sarapan yang mengandung karbohidrat kompleks, protein, dan serat seperti roti gandum, telur, buah-buahan, atau oatmeal. Hindari sarapan dengan makanan tinggi gula sederhana karena dapat membuat tubuh cepat lelah kembali.</p>',
                    ],
                    [
                        'judul' => 'Kenali Tanda Dehidrasi dan Cara Mencegahnya',
                        'ringkasan' => 'Dehidrasi ringan sering tidak disadari namun dapat memengaruhi konsentrasi dan kesehatan ginjal jangka panjang.',
                        'konten' => '<p>Tubuh manusia terdiri dari sekitar 60% air, sehingga kekurangan cairan dapat mengganggu berbagai fungsi organ. Tanda-tanda dehidrasi ringan antara lain rasa haus, mulut kering, urine berwarna gelap, dan mudah lelah.</p><p>Disarankan untuk minum air putih minimal 8 gelas per hari, lebih banyak lagi jika beraktivitas berat atau berada di cuaca panas. Konsumsi buah dan sayur yang mengandung banyak air juga dapat membantu menjaga hidrasi tubuh.</p>',
                    ],
                ],
            ],
            [
                'nama' => 'Info Layanan',
                'artikel' => [
                    [
                        'judul' => 'Jam Operasional Layanan Rawat Jalan Terbaru',
                        'ringkasan' => 'Informasi terbaru mengenai jam operasional poliklinik rawat jalan untuk memudahkan pasien merencanakan kunjungan.',
                        'konten' => '<p>Untuk memberikan pelayanan yang lebih optimal, layanan rawat jalan kini melayani pasien mulai pukul 07.00 hingga 20.00 WIB setiap hari kerja, dan pukul 08.00 hingga 14.00 WIB pada hari Sabtu.</p><p>Pasien disarankan untuk melakukan pendaftaran online terlebih dahulu agar tidak perlu mengantre lama di lokasi. Untuk kondisi gawat darurat, layanan IGD tetap beroperasi 24 jam setiap hari.</p>',
                    ],
                    [
                        'judul' => 'Layanan Medical Check Up untuk Karyawan Perusahaan',
                        'ringkasan' => 'Tersedia paket medical check up khusus untuk kebutuhan kesehatan kerja dan kepatuhan perusahaan.',
                        'konten' => '<p>Kami menyediakan layanan medical check up (MCU) yang dapat disesuaikan dengan kebutuhan perusahaan, mulai dari pemeriksaan dasar hingga paket lengkap meliputi laboratorium, rontgen, dan konsultasi dokter spesialis.</p><p>Layanan ini dapat dilakukan secara rombongan dengan jadwal yang fleksibel. Silakan hubungi bagian humas untuk informasi paket dan harga lebih lanjut.</p>',
                    ],
                    [
                        'judul' => 'Cara Mudah Mendaftar Pasien Baru secara Online',
                        'ringkasan' => 'Pendaftaran pasien baru kini dapat dilakukan secara online tanpa perlu datang langsung ke rumah sakit.',
                        'konten' => '<p>Untuk mempermudah proses administrasi, pasien baru kini dapat mendaftar secara online melalui website resmi rumah sakit. Cukup siapkan KTP, kartu BPJS (jika ada), serta keluhan kesehatan yang ingin disampaikan.</p><p>Setelah pendaftaran online berhasil, pasien akan mendapatkan nomor antrean dan dapat langsung menuju poliklinik tujuan pada waktu yang telah ditentukan tanpa perlu mengantre di loket pendaftaran.</p>',
                    ],
                ],
            ],
            [
                'nama' => 'Berita & Kegiatan',
                'artikel' => [
                    [
                        'judul' => 'Pelaksanaan Bakti Sosial dan Pemeriksaan Kesehatan Gratis',
                        'ringkasan' => 'Sebagai bentuk kepedulian terhadap masyarakat sekitar, rumah sakit mengadakan kegiatan bakti sosial rutin.',
                        'konten' => '<p>Dalam rangka memperingati hari kesehatan, tim medis mengadakan kegiatan bakti sosial berupa pemeriksaan kesehatan gratis, donor darah, dan edukasi pola hidup sehat bagi masyarakat di sekitar lingkungan rumah sakit.</p><p>Kegiatan ini disambut baik oleh warga dan diharapkan dapat terus dilaksanakan secara berkala sebagai wujud kontribusi nyata terhadap kesehatan masyarakat.</p>',
                    ],
                    [
                        'judul' => 'Pelatihan Internal Penanganan Kegawatdaruratan bagi Tenaga Medis',
                        'ringkasan' => 'Demi meningkatkan kualitas pelayanan, tenaga medis mengikuti pelatihan rutin penanganan kasus gawat darurat.',
                        'konten' => '<p>Seluruh tenaga medis dan paramedis mengikuti pelatihan internal mengenai penanganan kasus gawat darurat, termasuk simulasi Basic Life Support (BLS) dan Advanced Cardiac Life Support (ACLS).</p><p>Pelatihan ini bertujuan untuk memastikan seluruh staf medis selalu siap dan kompeten dalam menangani situasi darurat demi keselamatan pasien.</p>',
                    ],
                    [
                        'judul' => 'Peringatan Hari Kesehatan Nasional di Lingkungan Rumah Sakit',
                        'ringkasan' => 'Berbagai kegiatan edukatif digelar untuk menyambut Hari Kesehatan Nasional tahun ini.',
                        'konten' => '<p>Untuk menyambut Hari Kesehatan Nasional, rumah sakit mengadakan rangkaian kegiatan berupa seminar kesehatan, lomba kebersihan antar ruangan, serta apresiasi kepada tenaga medis dan non-medis atas dedikasinya selama ini.</p><p>Kegiatan ini diharapkan dapat meningkatkan semangat seluruh civitas rumah sakit dalam memberikan pelayanan terbaik kepada masyarakat.</p>',
                    ],
                ],
            ],
        ];

        foreach (RumahSakit::all() as $rs) {
            foreach ($kategoriData as $index => $kat) {
                $kategori = KategoriArtikel::create([
                    'rumah_sakit_id' => $rs->id,
                    'nama'           => $kat['nama'],
                    'slug'           => Str::slug($kat['nama']),
                ]);

                foreach ($kat['artikel'] as $artikelIndex => $a) {
                    Artikel::create([
                        'rumah_sakit_id'       => $rs->id,
                        'kategori_artikel_id'  => $kategori->id,
                        'judul'                => $a['judul'],
                        'slug'                 => Str::slug($a['judul']),
                        'ringkasan'            => $a['ringkasan'],
                        'konten'               => $a['konten'],
                        'gambar'               => null,
                        'penulis'              => 'Tim ' . $rs->nama,
                        'tanggal_publish'      => now()->subDays(($index * 3) + $artikelIndex),
                        'unggulan'             => $index === 0 && $artikelIndex === 0,
                        'aktif'                => true,
                    ]);
                }
            }
        }
    }
}
