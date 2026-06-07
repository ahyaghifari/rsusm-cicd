<?php

namespace Database\Seeders;

use App\Models\PoliKlinik;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PoliKlinikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Klinik Spesialis Anak',
                'deskripsi' => 'Memberikan layanan kesehatan komprehensif untuk bayi, anak, hingga remaja. Ditangani oleh dokter spesialis anak yang ramah dan berpengalaman untuk memastikan tumbuh kembang buah hati Anda berjalan optimal.'
            ],
            [
                'nama' => 'Klinik Spesialis Penyakit Dalam',
                'deskripsi' => 'Layanan diagnostik dan penanganan untuk berbagai masalah kesehatan organ dalam orang dewasa. Kami berdedikasi untuk memberikan perawatan holistik bagi penyakit kronis maupun akut dengan pendekatan medis terkini.'
            ],
            [
                'nama' => 'Klinik Spesialis Bedah',
                'deskripsi' => 'Menyediakan layanan konsultasi dan tindakan bedah umum dengan dukungan fasilitas kamar operasi berstandar tinggi. Keselamatan, kenyamanan, serta pemulihan pasien adalah prioritas utama kami.'
            ],
            [
                'nama' => 'Klinik Spesialis Orthopaedi & Traumatology',
                'deskripsi' => 'Fokus pada penanganan gangguan tulang, sendi, dan cedera fisik. Kami membantu mengembalikan mobilitas dan kualitas hidup Anda melalui perawatan medis dan tindakan operatif yang presisi.'
            ],
            [
                'nama' => 'Klinik Spesialis Kebidanan & Kandungan',
                'deskripsi' => 'Mendampingi setiap fase kesehatan wanita, mulai dari program kehamilan, perawatan masa kandungan, hingga persalinan. Kami hadir untuk memberikan layanan yang aman dan nyaman bagi ibu dan janin.'
            ],
            [
                'nama' => 'Klinik Spesialis Saraf',
                'deskripsi' => 'Penanganan komprehensif untuk gangguan sistem saraf, otak, dan tulang belakang. Menggunakan pendekatan medis mutakhir untuk mendiagnosis dan merawat berbagai kondisi neurologis.'
            ],
            [
                'nama' => 'Klinik Spesialis THT',
                'deskripsi' => 'Solusi medis terpercaya untuk berbagai gangguan pada Telinga, Hidung, dan Tenggorokan. Layanan kami mencakup pemeriksaan endoskopi rutin hingga tindakan khusus oleh spesialis berpengalaman.'
            ],
            [
                'nama' => 'Klinik Spesialis Paru',
                'deskripsi' => 'Pelayanan khusus untuk mendiagnosis dan mengobati berbagai penyakit sistem pernapasan dan paru-paru. Kami berkomitmen penuh untuk membantu Anda bernapas lebih lega dan hidup lebih sehat.'
            ],
            [
                'nama' => 'Klinik Spesialis Jantung',
                'deskripsi' => 'Perawatan kardiologi yang komprehensif, mulai dari deteksi dini, pencegahan, hingga rehabilitasi penyakit jantung. Didukung oleh teknologi medis terkini demi menjaga irama jantung Anda tetap sehat.'
            ],
            [
                'nama' => 'Klinik Spesialis Kulit & Kelamin',
                'deskripsi' => 'Menangani berbagai permasalahan kesehatan kulit, rambut, kuku, serta penyakit kelamin. Kami memberikan solusi medis teruji maupun perawatan estetika yang aman dan terpercaya.'
            ],
            [
                'nama' => 'Klinik Spesialis Mata',
                'deskripsi' => 'Menjaga kesehatan penglihatan Anda dengan layanan pemeriksaan mata komprehensif. Mulai dari penanganan katarak hingga gangguan refraksi, percayakan jendela dunia Anda pada spesialis kami.'
            ],
            [
                'nama' => 'Klinik Spesialis Jiwa (Psikiater)',
                'deskripsi' => 'Ruang aman dan nyaman untuk mendiskusikan kesehatan mental Anda. Kami menyediakan layanan terapi dan pengobatan psikiatri profesional untuk membantu Anda mencapai kesejahteraan emosional yang seimbang.'
            ],
            [
                'nama' => 'Klinik Spesialis Rehabilitasi',
                'deskripsi' => 'Program pemulihan medis yang dirancang khusus untuk mengembalikan fungsi gerak tubuh pasca cedera, stroke, atau sakit. Kami siap mendampingi Anda untuk kembali beraktivitas dengan optimal.'
            ],
            [
                'nama' => 'Klinik Spesialis Bedah Saraf',
                'deskripsi' => 'Penanganan tindakan operatif tingkat lanjut untuk sistem saraf pusat dan perifer. Tim bedah saraf kami selalu mengutamakan akurasi, presisi, dan kehati-hatian demi hasil klinis yang maksimal.'
            ],
            [
                'nama' => 'Klinik Spesialis Urologi',
                'deskripsi' => 'Layanan spesifik untuk mengatasi masalah pada sistem saluran kemih dan organ reproduksi pria. Kami menawarkan diagnosis yang akurat dan berbagai opsi perawatan minimal invasif.'
            ],
            [
                'nama' => 'Klinik Layanan Psikologi',
                'deskripsi' => 'Dukungan psikologis profesional untuk membantu Anda menghadapi berbagai tantangan hidup, masalah perilaku, dan kendala perkembangan. Kami hadir sebagai pendengar dan pembimbing yang objektif.'
            ],
            [
                'nama' => 'Klinik Gigi Umum',
                'deskripsi' => 'Layanan perawatan gigi dan mulut dasar, meliputi pemeriksaan rutin, pembersihan karang gigi (scaling), hingga penambalan. Langkah awal untuk menjaga senyum sehat Anda setiap hari.'
            ],
            [
                'nama' => 'Klinik Gigi Anak',
                'deskripsi' => 'Perawatan kesehatan gigi yang dirancang khusus dengan pendekatan psikologis yang ramah anak. Kami memastikan pengalaman ke dokter gigi menjadi momen yang menyenangkan dan bebas rasa takut.'
            ],
            [
                'nama' => 'Klinik Endodonsi (Konservasi Gigi)',
                'deskripsi' => 'Berfokus pada upaya mempertahankan gigi asli Anda selama mungkin. Meliputi perawatan saluran akar (root canal) dan restorasi gigi tingkat lanjut untuk mengatasi kerusakan gigi yang dalam.'
            ],
            [
                'nama' => 'Klinik Gigi Bedah Mulut',
                'deskripsi' => 'Penanganan tindakan bedah profesional untuk area rongga mulut, seperti pencabutan gigi bungsu (impaksi), pemasangan implan, dan penanganan kista gigi dengan standar keamanan tinggi.'
            ],
            [
                'nama' => 'Klinik Gigi Orthodonsi (Kawat Gigi)',
                'deskripsi' => 'Solusi medis untuk merapikan susunan gigi dan memperbaiki struktur rahang. Dapatkan senyum yang lebih sempurna dan fungsi kunyah yang ideal dengan perawatan kawat gigi yang tepat.'
            ],
            [
                'nama' => 'Klinik Gigi Prostodonsia (Gigi Palsu)',
                'deskripsi' => 'Layanan pembuatan dan pemasangan gigi tiruan (prostetik) untuk mengembalikan fungsi pengunyahan, memperbaiki estetika wajah, dan mengembalikan rasa percaya diri akibat kehilangan gigi.'
            ],
        ];

        foreach ($data as $item) {
            PoliKlinik::create([
                'rumah_sakit_id' => 1,
                'nama'           => $item['nama'],
                'slug'           => Str::slug($item['nama']),
                'gambar'         => null,
                'deskripsi'      => $item['deskripsi'],
                'aktif'          => true,
            ]);
        }
    }
}