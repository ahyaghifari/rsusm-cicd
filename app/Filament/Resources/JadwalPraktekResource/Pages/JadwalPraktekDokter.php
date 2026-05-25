<?php

namespace App\Filament\Resources\JadwalPraktekResource\Pages;

use App\Filament\Resources\JadwalPraktekResource;
use App\Models\Dokter;
use App\Models\JadwalPraktek;
use App\Models\RumahSakit;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class JadwalPraktekDokter extends Page
{
    protected static string $resource = JadwalPraktekResource::class;

    protected static string $view = 'filament.resources.jadwal-praktek-resource.pages.jadwal-praktek-dokter';

    // Deklarasi properti state management Livewire wajib sesuai spesifikasi multi-hospital
    public ?int $selectedRumahSakitId = null; // Menampung ID Rumah Sakit pilihan (hanya untuk Superadmin)
    public ?int $selectedDokterId = null;   // Menampung ID Dokter yang sedang dipilih
    public bool $isEditing = false;         // Flag penanda apakah halaman sedang dalam mode edit (repeater) atau preview
    public ?string $namaDokter = null;
    public array $schedule = [];            // Array penampung data jadwal 7 hari yang disinkronisasikan ke Form / Preview

    /**
     * Method lifecycle Livewire yang berjalan otomatis saat komponen pertama kali dimuat (Inisialisasi).
     */
    public function mount(): void
    {
        // Jika pengguna login adalah Admin RS (memiliki rumah_sakit_id), kunci pilihan dropdown RS dengan memaksa state bernilai null
        if (!JadwalPraktekResource::isSuperAdmin()) {
            $this->selectedRumahSakitId = null;
        }

        // Melakukan pre-fill skema form kosong agar siap menerima data state array schedule kelak
        $this->form->fill([
            'schedule' => $this->schedule,
        ]);
    }

    /**
     * Helper untuk mengambil ID Rumah Sakit yang aktif saat ini berdasarkan hak akses (Role-based scope).
     * * @return int|null
     */
    public function getActiveRumahSakitId(): ?int
    {
        // Jika Admin RS: langsung kembalikan ID dari database user terotentikasi
        if (!JadwalPraktekResource::isSuperAdmin()) {
            return (int) JadwalPraktekResource::rumahSakitId();
        }

        // Jika Superadmin: ambil nilai dari dropdown pilihan komponen UI ($selectedRumahSakitId)
        return $this->selectedRumahSakitId ? (int) $this->selectedRumahSakitId : null;
    }

    /**
     * Properti dinamis (Computed Property) untuk mengambil nama rumah sakit aktif guna keperluan tampilan UI badge.
     * * @return string|null
     */
    public function getActiveRumahSakitNameProperty(): ?string
    {
        $rumahSakitId = $this->getActiveRumahSakitId();
        if (! $rumahSakitId) {
            return null;
        }
        return RumahSakit::where('id', $rumahSakitId)->value('nama');
    }

    /**
     * Hook Livewire otomatis: Berjalan ketika nilai properti $selectedRumahSakitId diubah oleh pengguna di UI.
     * Berfungsi mereset seluruh data dokter dan jadwal terpilih demi keamanan lintas rumah sakit.
     */
    public function updatedSelectedRumahSakitId($value): void
    {
        if(empty($value)){
            $this->selectedDokterId = null;
        }

    }

    /**
     * Hook Livewire otomatis: Berjalan ketika nilai properti $selectedDokterId diubah oleh pengguna di UI.
     * Berfungsi memicu fungsi load data atau pengosongan state secara otomatis.
     */
    public function updatedSelectedDokterId($value): void
    {
        $this->isEditing = false;
        if (empty($value)) {
            $this->schedule = [];
            $this->form->fill(['schedule' => []]);
            return;
        }
        // Panggil fungsi inti untuk memuat data jadwal atau generate baru jika record belum ada
        $this->loadOrGenerateSchedule((int) $value);
    }

    /**
     * Logika Utama: Memuat data dari database atau membuat data template default 7 hari (Senin - Minggu).
     * * @param int $dokterId
     */
    public function loadOrGenerateSchedule(int $dokterId): void
    {
        // 1. Validasi Keamanan Lintas Rumah Sakit (Cross-Hospital Protection)
        $rumahSakitId = $this->getActiveRumahSakitId();
        if (! $rumahSakitId) {
            abort(403, 'Akses Ditolak: Rumah sakit aktif tidak teridentifikasi.');
        }

        $dokter = Dokter::find($dokterId);
        if (! $dokter || (int) $dokter->rumah_sakit_id !== $rumahSakitId) {
            abort(403, 'Akses Ditolak: Dokter tidak ditemukan pada rumah sakit ini.');
        }

        // 2. Definisi Array Urutan Hari yang Wajib Dipenuhi
        $daftarHari = JadwalPraktek::$hari;

        // 3. Looping untuk memastikan data 7 hari sudah terbentuk di database secara aman via firstOrCreate
        foreach ($daftarHari as $hari) {
            JadwalPraktek::firstOrCreate(
                [
                    'dokter_id' => $dokterId,
                    'hari' => $hari,
                ],
                [
                    'waktu_mulai' => null,
                    'waktu_selesai' => null,
                    'libur' => false,
                    'sesuai_perjanjian' => false,
                ]
            );
        }

        // 4. Ambil ulang seluruh data jadwal ter-generate dan urutkan menggunakan FIELD MySQL sesuai urutan wajib
        $this->schedule = JadwalPraktek::where('dokter_id', $dokterId)
            ->orderByRaw("FIELD(hari, 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU')")
            ->get()
            ->toArray();

        $this->namaDokter = $dokter->nama;

        // 5. Isi nilai state form Filament dengan data terurut tersebut agar siap diedit di dalam komponen repeater
        $this->form->fill([
            'schedule' => $this->schedule,
        ]);

    }

    /**
     * Mengubah mode tampilan halaman dari Preview Mode ke Edit Mode (Repeater).
     */
    public function startEdit(): void
    {
        if ($this->selectedDokterId) {
            $this->isEditing = true;
        }
    }

    /**
     * Membatalkan seluruh inputan sementara user, mengembalikan mode ke preview, dan memuat ulang data asli dari DB.
     */
    public function cancelEdit(): void
    {
        $this->isEditing = false;
        if ($this->selectedDokterId) {
            $this->loadOrGenerateSchedule((int) $this->selectedDokterId);
        }
        Notification::make()->title('Perubahan dibatalkan')->warning()->send();
    }

    /**
     * Logika Simpan Data Amankan (Replace-All Logic) dibungkus di dalam Database Transaction.
     */
    public function save(): void
    {
        // 1. Validasi Keamanan Pra-Operasi (Double Guard)
        $rumahSakitId = $this->getActiveRumahSakitId();
        if (! $rumahSakitId || ! $this->selectedDokterId) {
            abort(403, 'Akses Ditolak: Operasi tidak valid.');
        }

        $dokter = Dokter::find($this->selectedDokterId);
        if (! $dokter || (int) $dokter->rumah_sakit_id !== $rumahSakitId) {
            abort(403, 'Akses Ditolak: Validasi kepemilikan data gagal.');
        }

        // 2. Ekstrak data mentah dari skema form/repeater setelah melalui filter validasi dasar Filament
        $formData = $this->form->getState();

        // 3. Jalankan Database Transaction untuk mencegah inkonsistensi data jika terjadi error di tengah jalan
        DB::transaction(function () use ($formData, $rumahSakitId) {
            // Re-check kepemilikan data di dalam transaksi
            $dokterCheck = Dokter::where('id', $this->selectedDokterId)
                ->where('rumah_sakit_id', $rumahSakitId)
                ->first();

            if (! $dokterCheck) {
                throw new AccessDeniedException('Validasi keamanan gagal dalam transaksi.');
            }

            // Aksi Hapus: Menghapus total seluruh jadwal lama milik dokter_id terpilih saja (Aman & Tervalidasi)
            JadwalPraktek::where('dokter_id', $this->selectedDokterId)->delete();

            // Aksi Insert Ulang: Iterasi data dari form repeater satu per satu ke dalam tabel
            foreach ($formData['schedule'] as $row) {
                $isLibur = $row['libur'] ?? false;
                $waktuMulai = $row['waktu_mulai'] ?? null;
                $waktuSelesai = $row['waktu_selesai'] ?? null;

                // Aturan Validasi Kondisional: Jika tidak diset libur, maka input jam mulai & selesai sifatnya WAJIB
                if (! $isLibur && (empty($waktuMulai) || empty($waktuSelesai))) {
                    throw new \Exception("Hari " . $row['hari'] . " aktif, maka jam mulai dan jam selesai wajib diisi.");
                }

                JadwalPraktek::create([
                    'dokter_id' => $this->selectedDokterId,
                    'hari' => $row['hari'],
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $waktuSelesai,
                    'libur' => $isLibur,
                    'sesuai_perjanjian' => $row['sesuai_perjanjian'] ?? false,
                ]);
            }
        });

        // 4. Reset state, kembalikan ke mode preview, dan lakukan sinkronisasi data terbaru
        $this->isEditing = false;
        $this->loadOrGenerateSchedule((int) $this->selectedDokterId);

        // Tampilkan pesan sukses di pojok kanan layar via sistem notifikasi bawaan Filament
        Notification::make()->title('Jadwal berhasil diperbarui')->success()->send();
    }

    /**
     * Mendaftarkan multiple form di dalam satu halaman Filament
     */
    protected function getForms(): array
    {
        return [
            'form',       // Form default untuk Repeater (Mode Edit)
            'filterForm', // Form baru khusus untuk Filter Pemilihan
        ];
    }

    /**
     * Definisi struktur komponen form skema Filament (Digunakan saat variabel $isEditing bernilai true).
     * * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Menggunakan form jenis Repeater untuk memetakan input data dinamis berulang
                Repeater::make('schedule')
                    ->label('Atur Jadwal Mingguan')
                    ->schema([
                        // Dropdown Hari dikunci (disabled) agar user tidak bisa mengubah data index nama hari bawaan sistem
                        Select::make('hari')
                            ->options([
                                'SENIN' => 'SENIN',
                                'SELASA' => 'SELASA',
                                'RABU' => 'RABU',
                                'KAMIS' => 'KAMIS',
                                'JUMAT' => 'JUMAT',
                                'SABTU' => 'SABTU',
                                'MINGGU' => 'MINGGU',
                            ])
                            ->disabled()
                            ->dehydrated() // Memastikan data nama hari yang di-disable tetap dikirim ke server saat submit data
                            ->required(),
                        TimePicker::make('waktu_mulai')
                            ->label('Jam Mulai')
                            ->seconds(false)
                            ->required(fn ($get) => ! $get('libur')), // Validasi reaktif: Wajib diisi hanya jika toggle 'libur' tidak aktif
                        TimePicker::make('waktu_selesai')
                            ->label('Jam Selesai')
                            ->seconds(false)
                            ->required(false), // Validasi reaktif: Wajib diisi hanya jika toggle 'libur' tidak aktif
                         Toggle::make('sesuai_perjanjian')
                            ->label('Sesuai Perjanjian')
                            ->inline(false),
                        Toggle::make('libur')
                        ->label('Libur')
                        ->live() // Mematikan atau menghidupkan validasi reaktif jam secara realtime tanpa reload halaman
                        ->inline(false),
                    ])
                    ->columns(5)       // Membagi layout form repeater menjadi 5 kolom grid sejajar horizontal
                    ->addable(false)   // Mengunci baris: User dilarang keras menambahkan baris baru di UI (Sesuai Aturan Dokumen)
                    ->deletable(false) // Mengunci baris: User dilarang keras menghapus baris bawaan di UI (Sesuai Aturan Dokumen)
                    ->reorderable(false) // Menonaktifkan fitur drag-and-drop urutan baris
            ]);
    }

    public function filterForm(Form $form) : Form 
    {
        return $form->schema([
            Select::make('selectedRumahSakitId')
                ->label('Rumah Sakit')
                ->placeholder('- Pilih Rumah Sakit -')
                ->options(function(){
                    return RumahSakit::all()->pluck('nama', 'id');
                })
                ->required(fn() => JadwalPraktekResource::isSuperAdmin())
                ->visible(fn() => JadwalPraktekResource::isSuperAdmin())
                ->live(condition: JadwalPraktekResource::isSuperAdmin())
                ->preload(),
            Select::make('selectedDokterId')
                ->label('Dokter')
                ->required()
                ->placeholder('- Pilih Dokter -')
                ->options(function(){
                    return Dokter::where('rumah_sakit_id', $this->getActiveRumahSakitId())->get()->pluck('nama', 'id');
                })
                ->visible(fn () => $this->getActiveRumahSakitId())
                ->live()
                ->preload(),
        ])
        ->statePath('')
        ->columns(2)
        ;
    }
}
