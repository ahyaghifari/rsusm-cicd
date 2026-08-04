# Planning Data Poliklinik

Berikut adalah instruksi teknis untuk mengimplementasikan modul data Poliklinik menggunakan Laravel dan Filament. Instruksi ini siap dikerjakan oleh programmer atau AI Model.

## 1. Migrasi Database

Buatkan file migrasi untuk tabel `poliklinik` dengan spesifikasi kolom sebagai berikut:

- `id` (integer, auto increment, primary key)
- `unit_layanan_id` (integer, foreign key ke tabel `unit_layanan`)
- `nama` (varchar 255)
- `slug` (varchar 255, unique)
- `gambar` (varchar 255, nullable)
- `deskripsi` (text)
- `aktif` (boolean, default true)
- `timestamps` (created_at, updated_at)

## 2. Model `PoliKlinik`

Buatkan model Eloquent dengan nama `PoliKlinik`.

- **Fillable**: Semua kolom di atas kecuali `id`.
- **Relasi**: Tambahkan relasi `belongsTo` ke model `UnitLayanan` melalui kolom `unit_layanan_id`.

## 3. Filament Resource

Generate resource Filament untuk model `PoliKlinik` menggunakan flag `--simple` (modal-based CRUD).

- **Command**: `php artisan make:filament-resource PoliKlinikResource --simple`

### Konfigurasi Form

Form wajib dikonfigurasi sebagai berikut:

1. **Pemilihan Rumah Sakit (filter helper)**: Sebelum field `unit_layanan_id`, tambahkan field `Select` untuk memilih Rumah Sakit terlebih dahulu (sumber dari tabel `rumah_sakit`). Field ini bersifat **reactive** dan digunakan sebagai filter agar opsi `unit_layanan_id` hanya menampilkan data unit layanan yang berasal dari rumah sakit yang dipilih. Field ini **tidak** disimpan ke database (gunakan `dehydrated(false)`).

   Contoh implementasi:

   ```php
   Select::make('rumah_sakit_id')
       ->label('Rumah Sakit')
       ->options(RumahSakit::pluck('nama', 'id'))
       ->live()
       ->dehydrated(false)
       ->afterStateUpdated(fn (Set $set) => $set('unit_layanan_id', null)),
   ```

2. **Field `unit_layanan_id`**: Gunakan `Select` dengan opsi yang difilter berdasarkan `rumah_sakit_id` yang dipilih di atas. Tampilkan nama unit layanan sebagai label. Wajib diisi.

   Contoh implementasi:

   ```php
   Select::make('unit_layanan_id')
       ->label('Unit Layanan')
       ->options(function (Get $get) {
           $rumahSakitId = $get('rumah_sakit_id');
           if (!$rumahSakitId) return [];
           return UnitLayanan::where('rumah_sakit_id', $rumahSakitId)->pluck('nama', 'id');
       })
       ->required()
       ->searchable(),
   ```

   > **Catatan**: Pastikan import `use Filament\Forms\Get;` dan `use Filament\Forms\Set;` sudah ditambahkan di bagian atas file Resource.

3. **Field `gambar`**: Gunakan `FileUpload` dengan method `->image()` agar hanya menerima file gambar.

   ```php
   FileUpload::make('gambar')
       ->image()
       ->nullable(),
   ```

4. Field lain (`nama`, `slug`, `deskripsi`, `aktif`) dikonfigurasi secara standar sesuai tipe datanya.

> **Catatan penting**: Logika pemilihan Rumah Sakit → Unit Layanan ini harus diterapkan **baik di form Create maupun Edit**.

## 4. Seeder Database

Buatkan seeder dengan nama `PoliKlinikSeeder`. Isi `unit_layanan_id` dengan nilai `1` untuk semua data. Slug dibuat dari nama menggunakan format _lowercase kebab-case_ (contoh: `klinik-spesialis-anak`).

### Data Seeder

| #   | Nama                                       | Slug                                     | Deskripsi                                                                                                                                                                                                           |
| --- | ------------------------------------------ | ---------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | Klinik Spesialis Anak                      | klinik-spesialis-anak                    | Memberikan layanan kesehatan komprehensif untuk bayi, anak, hingga remaja. Ditangani oleh dokter spesialis anak yang ramah dan berpengalaman untuk memastikan tumbuh kembang buah hati Anda berjalan optimal.       |
| 2   | Klinik Spesialis Penyakit Dalam            | klinik-spesialis-penyakit-dalam          | Layanan diagnostik dan penanganan untuk berbagai masalah kesehatan organ dalam orang dewasa. Kami berdedikasi untuk memberikan perawatan holistik bagi penyakit kronis maupun akut dengan pendekatan medis terkini. |
| 3   | Klinik Spesialis Bedah                     | klinik-spesialis-bedah                   | Menyediakan layanan konsultasi dan tindakan bedah umum dengan dukungan fasilitas kamar operasi berstandar tinggi. Keselamatan, kenyamanan, serta pemulihan pasien adalah prioritas utama kami.                      |
| 4   | Klinik Spesialis Orthopaedi & Traumatology | klinik-spesialis-orthopaedi-traumatology | Fokus pada penanganan gangguan tulang, sendi, dan cedera fisik. Kami membantu mengembalikan mobilitas dan kualitas hidup Anda melalui perawatan medis dan tindakan operatif yang presisi.                           |
| 5   | Klinik Spesialis Kebidanan & Kandungan     | klinik-spesialis-kebidanan-kandungan     | Mendampingi setiap fase kesehatan wanita, mulai dari program kehamilan, perawatan masa kandungan, hingga persalinan. Kami hadir untuk memberikan layanan yang aman dan nyaman bagi ibu dan janin.                   |
| 6   | Klinik Spesialis Saraf                     | klinik-spesialis-saraf                   | Penanganan komprehensif untuk gangguan sistem saraf, otak, dan tulang belakang. Menggunakan pendekatan medis mutakhir untuk mendiagnosis dan merawat berbagai kondisi neurologis.                                   |
| 7   | Klinik Spesialis THT                       | klinik-spesialis-tht                     | Solusi medis terpercaya untuk berbagai gangguan pada Telinga, Hidung, dan Tenggorokan. Layanan kami mencakup pemeriksaan endoskopi rutin hingga tindakan khusus oleh spesialis berpengalaman.                       |
| 8   | Klinik Spesialis Paru                      | klinik-spesialis-paru                    | Pelayanan khusus untuk mendiagnosis dan mengobati berbagai penyakit sistem pernapasan dan paru-paru. Kami berkomitmen penuh untuk membantu Anda bernapas lebih lega dan hidup lebih sehat.                          |
| 9   | Klinik Spesialis Jantung                   | klinik-spesialis-jantung                 | Perawatan kardiologi yang komprehensif, mulai dari deteksi dini, pencegahan, hingga rehabilitasi penyakit jantung. Didukung oleh teknologi medis terkini demi menjaga irama jantung Anda tetap sehat.               |
| 10  | Klinik Spesialis Kulit & Kelamin           | klinik-spesialis-kulit-kelamin           | Menangani berbagai permasalahan kesehatan kulit, rambut, kuku, serta penyakit kelamin. Kami memberikan solusi medis teruji maupun perawatan estetika yang aman dan terpercaya.                                      |
| 11  | Klinik Spesialis Mata                      | klinik-spesialis-mata                    | Menjaga kesehatan penglihatan Anda dengan layanan pemeriksaan mata komprehensif. Mulai dari penanganan katarak hingga gangguan refraksi, percayakan jendela dunia Anda pada spesialis kami.                         |
| 12  | Klinik Spesialis Jiwa (Psikiater)          | klinik-spesialis-jiwa                    | Ruang aman dan nyaman untuk mendiskusikan kesehatan mental Anda. Kami menyediakan layanan terapi dan pengobatan psikiatri profesional untuk membantu Anda mencapai kesejahteraan emosional yang seimbang.           |
| 13  | Klinik Spesialis Rehabilitasi              | klinik-spesialis-rehabilitasi            | Program pemulihan medis yang dirancang khusus untuk mengembalikan fungsi gerak tubuh pasca cedera, stroke, atau sakit. Kami siap mendampingi Anda untuk kembali beraktivitas dengan optimal.                        |
| 14  | Klinik Spesialis Bedah Saraf               | klinik-spesialis-bedah-saraf             | Penanganan tindakan operatif tingkat lanjut untuk sistem saraf pusat dan perifer. Tim bedah saraf kami selalu mengutamakan akurasi, presisi, dan kehati-hatian demi hasil klinis yang maksimal.                     |
| 15  | Klinik Spesialis Urologi                   | klinik-spesialis-urologi                 | Layanan spesifik untuk mengatasi masalah pada sistem saluran kemih dan organ reproduksi pria. Kami menawarkan diagnosis yang akurat dan berbagai opsi perawatan minimal invasif.                                    |
| 16  | Klinik Layanan Psikologi                   | klinik-layanan-psikologi                 | Dukungan psikologis profesional untuk membantu Anda menghadapi berbagai tantangan hidup, masalah perilaku, dan kendala perkembangan. Kami hadir sebagai pendengar dan pembimbing yang objektif.                     |
| 17  | Klinik Gigi Umum                           | klinik-gigi-umum                         | Layanan perawatan gigi dan mulut dasar, meliputi pemeriksaan rutin, pembersihan karang gigi (scaling), hingga penambalan. Langkah awal untuk menjaga senyum sehat Anda setiap hari.                                 |
| 18  | Klinik Gigi Anak                           | klinik-gigi-anak                         | Perawatan kesehatan gigi yang dirancang khusus dengan pendekatan psikologis yang ramah anak. Kami memastikan pengalaman ke dokter gigi menjadi momen yang menyenangkan dan bebas rasa takut.                        |
| 19  | Klinik Endodonsi (Konservasi Gigi)         | klinik-endodonsi-konservasi-gigi         | Berfokus pada upaya mempertahankan gigi asli Anda selama mungkin. Meliputi perawatan saluran akar (root canal) dan restorasi gigi tingkat lanjut untuk mengatasi kerusakan gigi yang dalam.                         |
| 20  | Klinik Gigi Bedah Mulut                    | klinik-gigi-bedah-mulut                  | Penanganan tindakan bedah profesional untuk area rongga mulut, seperti pencabutan gigi bungsu (impaksi), pemasangan implan, dan penanganan kista gigi dengan standar keamanan tinggi.                               |
| 21  | Klinik Gigi Orthodonsi (Kawat Gigi)        | klinik-gigi-orthodonsi                   | Solusi medis untuk merapikan susunan gigi dan memperbaiki struktur rahang. Dapatkan senyum yang lebih sempurna dan fungsi kunyah yang ideal dengan perawatan kawat gigi yang tepat.                                 |
| 22  | Klinik Gigi Prostodonsia (Gigi Palsu)      | klinik-gigi-prostodonsia                 | Layanan pembuatan dan pemasangan gigi tiruan (prostetik) untuk mengembalikan fungsi pengunyahan, memperbaiki estetika wajah, dan mengembalikan rasa percaya diri akibat kehilangan gigi.                            |

> **Catatan**: Kolom `gambar` dikosongkan (null) untuk semua data seeder. Kolom `aktif` di-set `true` secara default.
