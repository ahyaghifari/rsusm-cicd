<?php

namespace App\Filament\Dokter\Pages;

use App\Enums\PengirimPesan;
use Illuminate\Support\Str;
use App\Enums\StatusSesiKonsultasi;
use App\Events\PesanDikirim;
use App\Events\SesiStatusBerubah;
use App\Jobs\SendWebPushNotification;
use App\Models\Dokter;
use App\Models\SesiKonsultasi;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class KonsultasiDashboard extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $title          = 'Dashboard Konsultasi';
    protected static ?string $navigationLabel = 'Konsultasi';
    protected static ?int    $navigationSort  = 1;

    protected static string $view = 'filament.dokter.pages.konsultasi-dashboard';

    #[Locked]
    public ?Dokter $dokter = null;

    public ?int $sesiAktifId = null;

    public bool $tersediaKonsultasi = false;

    /**
     * Selalu string (bukan nullable) — placeholder dinamis #[On('echo:konsultasi.{sesiAktifToken},...')]
     * tidak bisa di-resolve Livewire jika nilainya null. String kosong berarti
     * "tidak ada sesi aktif" dan menghasilkan channel yang tidak pernah disiarkan apa pun.
     */
    public string $sesiAktifToken = '';

    #[Validate('required|string|max:1000')]
    public string $balasan = '';

    public bool   $tampilFormAkhiri  = false;
    public string $kesimpulanInput   = '';

    public function mount(): void
    {
        $this->dokter = Dokter::where('user_id', filament()->auth()->id())->first();

        abort_if(! $this->dokter, 403, 'Akun Anda belum terhubung dengan data dokter manapun. Hubungi admin rumah sakit.');

        $sesi = $this->antrean()->firstWhere('status', StatusSesiKonsultasi::BERLANGSUNG);
        $this->pilihSesiInternal($sesi?->id);

        $this->tersediaKonsultasi = $this->dokter->tersedia_konsultasi;
    }

    /**
     * Antrean sesi milik dokter ini — BERLANGSUNG (sedang ditangani) tampil
     * lebih dulu, lalu MENUNGGU terurut FIFO (created_at ASC).
     */
    public function antrean(): Collection
    {
        return SesiKonsultasi::query()
            ->where('dokter_id', $this->dokter->id)
            ->whereIn('status', [StatusSesiKonsultasi::MENUNGGU, StatusSesiKonsultasi::BERLANGSUNG])
            ->with('latestPesan')
            ->withCount([
                'pesan as belum_dibaca' => function ($q) {
                    $q->where('pengirim', PengirimPesan::PASIEN->value)
                      ->whereRaw('`konsultasi_pesan`.`created_at` > IFNULL(`sesi_konsultasi`.`dokter_baca_at`, "1970-01-01 00:00:00")');
                },
            ])
            ->orderByRaw("FIELD(status, 'BERLANGSUNG', 'MENUNGGU')")
            ->orderBy('created_at')
            ->get();
    }

    public function sesiAktif(): ?SesiKonsultasi
    {
        if (! $this->sesiAktifId) {
            return null;
        }

        return SesiKonsultasi::with(['pesan', 'rumahSakit'])->find($this->sesiAktifId);
    }

    public function pilihSesi(int $sesiId): void
    {
        $this->resetValidation();
        $this->reset('balasan');
        $this->pilihSesiInternal($sesiId);
    }

    protected function pilihSesiInternal(?int $sesiId): void
    {
        $sesi = $sesiId ? SesiKonsultasi::find($sesiId) : null;

        $this->sesiAktifId      = $sesi?->id;
        $this->sesiAktifToken   = $sesi?->token ?? '';
        $this->tampilFormAkhiri = false;
        $this->kesimpulanInput  = '';

        if ($sesi) {
            $sesi->update(['dokter_baca_at' => now()]);
        }
    }

    /**
     * Notifikasi real-time saat ada sesi baru / perubahan status untuk dokter
     * ini — lihat routes/channels.php untuk otorisasi private channel-nya.
     * Cukup memicu render ulang; antrean() & sesiAktif() membaca langsung dari DB.
     */
    #[On('echo-private:konsultasi.dokter.{dokter.id},SesiStatusBerubah')]
    public function antreanBerubah(): void
    {
        if ($this->sesiAktifId && ! $this->antrean()->contains('id', $this->sesiAktifId)) {
            $this->pilihSesiInternal(null);
        }
    }

    #[On('echo:konsultasi.{sesiAktifToken},PesanDikirim')]
    public function pesanMasuk(): void
    {
        // Dokter sedang melihat sesi ini → tandai terbaca seketika
        if ($this->sesiAktifId) {
            SesiKonsultasi::where('id', $this->sesiAktifId)->update(['dokter_baca_at' => now()]);
        }
        // memicu render ulang — riwayat dibaca langsung dari relasi sesiAktif->pesan
    }

    #[On('echo:konsultasi.{sesiAktifToken},SesiStatusBerubah')]
    public function sesiAktifBerubah(): void
    {
        // memicu render ulang — status sesi aktif dibaca langsung dari DB
    }

    public function terima(int $sesiId): void
    {
        $sesi = SesiKonsultasi::where('dokter_id', $this->dokter->id)
            ->where('status', StatusSesiKonsultasi::MENUNGGU)
            ->findOrFail($sesiId);

        $sesi->update([
            'status'       => StatusSesiKonsultasi::BERLANGSUNG,
            'mulai_at'     => now(),
            'berakhir_at'  => now()->addMinutes($sesi->durasi_menit),
            'dibalas_oleh' => filament()->auth()->id(),
        ]);

        broadcast(new SesiStatusBerubah($sesi))->toOthers();

        $this->pilihSesiInternal($sesi->id);

        Notification::make()->title('Sesi konsultasi diterima')->success()->send();
    }

    public function kirimBalasan(): void
    {
        $this->validate();

        $sesi = $this->sesiAktif();

        abort_unless($sesi && $sesi->status === StatusSesiKonsultasi::BERLANGSUNG, 403);

        $pesan = $sesi->pesan()->create([
            'pengirim' => PengirimPesan::DOKTER,
            'isi'      => $this->balasan,
        ]);

        broadcast(new PesanDikirim($sesi, $pesan))->toOthers();

        if ($sesi->push_subscription) {
            $chatUrl = route('rumahsakit.konsultasi', [
                'rumahsakit' => $sesi->rumahSakit->slug,
                'sesi'       => $sesi->token,
            ]);
            SendWebPushNotification::dispatch(
                $sesi->id,
                'Pesan dari ' . $this->dokter->nama,
                $pesan->isi,
                $chatUrl,
                $sesi->token,
            );
        }

        $this->reset('balasan');
    }

    public function siapAkhiri(): void
    {
        $this->tampilFormAkhiri = true;
    }

    public function batalAkhiri(): void
    {
        $this->tampilFormAkhiri = false;
        $this->kesimpulanInput  = '';
    }

    public function akhiriDenganKesimpulan(): void
    {
        $sesi = SesiKonsultasi::where('dokter_id', $this->dokter->id)
            ->where('status', StatusSesiKonsultasi::BERLANGSUNG)
            ->findOrFail($this->sesiAktifId);

        $sesi->update([
            'status'      => StatusSesiKonsultasi::SELESAI,
            'berakhir_at' => now(),
            'kesimpulan'  => trim($this->kesimpulanInput) ?: null,
        ]);

        broadcast(new SesiStatusBerubah($sesi))->toOthers();

        $this->tampilFormAkhiri = false;
        $this->kesimpulanInput  = '';
        $this->pilihSesiInternal(null);

        Notification::make()->title('Sesi konsultasi diakhiri')->success()->send();
    }

    /**
     * Hook lifecycle Livewire — terpanggil otomatis tiap kali $tersediaKonsultasi
     * berubah (lewat toggle Filament yang di-entangle ke variabel ini di Blade).
     * Menjaga $tersediaKonsultasi sebagai satu-satunya sumber state UI, terpisah
     * dari atribut model — perubahan baru ditulis ke DB di sini, bukan langsung dari view.
     */
    public function updatedTersediaKonsultasi(bool $value): void
    {
        $this->dokter->update(['tersedia_konsultasi' => $value]);

        Notification::make()
            ->title($value
                ? 'Anda kini tersedia menerima konsultasi baru'
                : 'Anda kini tidak tersedia untuk konsultasi baru')
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        return [
            'antrean'   => $this->antrean(),
            'sesiAktif' => $this->sesiAktif(),
        ];
    }
}
