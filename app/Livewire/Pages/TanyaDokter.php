<?php

namespace App\Livewire\Pages;

use App\Enums\StatusSesiKonsultasi;
use App\Events\SesiStatusBerubah;
use App\Livewire\RsPortalComponent;
use App\Models\Dokter;
use App\Models\SesiKonsultasi;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;

class TanyaDokter extends RsPortalComponent
{
    private const BURST_LIMIT   = 2;   // maks. sesi baru dalam jendela singkat
    private const BURST_MINUTES = 30;
    private const DAILY_LIMIT   =10;
    private const DAILY_HOURS   = 24;

    public ?int $dokterDipilih = null;

    #[Validate('required|string|max:100')]
    public string $nama = '';

    #[Validate('required|string|max:100')]
    public string $kontak = '';

    public function mount(): void
    {
        abort_unless($this->rs->tanya_dokter_aktif, 404);

        $token = request()->cookie($this->cookieSesiKey());

        if ($token) {
            $sesiAktif = SesiKonsultasi::query()
                ->where('rumah_sakit_id', $this->rs->id)
                ->where('token', $token)
                ->whereIn('status', [StatusSesiKonsultasi::MENUNGGU, StatusSesiKonsultasi::BERLANGSUNG])
                ->first();

            if ($sesiAktif) {
                $this->redirect(route('rumahsakit.konsultasi', [
                    'rumahsakit' => $this->rs->slug,
                    'sesi'       => $sesiAktif->token,
                ]));
                return;
            }

            // Token tersimpan tapi sesinya sudah selesai/kedaluwarsa — bersihkan agar tidak terus dicek tiap kunjungan.
            Cookie::queue(Cookie::forget($this->cookieSesiKey()));
        }

        $this->seo('Tanya Dokter', 'Konsultasi chat langsung dengan dokter kami di ' . $this->rs->nama . '.');
    }

    /**
     * Nama cookie yang menyimpan token sesi aktif milik pasien ini, di-scope per rumah sakit
     * supaya pasien tetap bisa membuka sesi terpisah di rumah sakit lain.
     */
    private function cookieSesiKey(): string
    {
        return 'konsultasi_sesi_' . $this->rs->id;
    }

    public function pilihDokter(int $dokterId): void
    {
        $this->resetValidation();
        $this->reset('nama', 'kontak');
        $this->dokterDipilih = $dokterId;
    }

    public function batalkanPilihan(): void
    {
        $this->resetValidation();
        $this->reset('dokterDipilih', 'nama', 'kontak');
    }

    public function mulaiSesi(): void
    {
        $this->validate();

        if (! $this->dokterDipilih) {
            $this->addError('dokterDipilih', 'Silakan pilih dokter terlebih dahulu.');
            return;
        }

        $id       = request()->ip() . '|' . session()->getId();
        $burstKey = "konsultasi-burst:{$id}";
        $dailyKey = "konsultasi-daily:{$id}";

        // if (RateLimiter::tooManyAttempts($burstKey, self::BURST_LIMIT) ||
        //     RateLimiter::tooManyAttempts($dailyKey, self::DAILY_LIMIT)) {
        //     $this->addError('nama', 'Anda telah mencapai batas pembuatan sesi konsultasi. Silakan coba lagi nanti.');
        //     return;
        // }

        // Validasi ulang ketersediaan dokter saat ini juga (bukan hanya saat render)
        // — mencegah race condition jika dokter baru saja menonaktifkan diri.
        $dokter = Dokter::where('rumah_sakit_id', $this->rs->id)
            ->where('dapat_konsultasi', true)
            ->where('tersedia_konsultasi', true)
            ->find($this->dokterDipilih);

        if (! $dokter) {
            $this->addError('dokterDipilih', 'Mohon maaf, dokter ini baru saja menjadi tidak tersedia. Silakan pilih dokter lain.');
            $this->dokterDipilih = null;
            return;
        }

        RateLimiter::hit($burstKey, self::BURST_MINUTES * 60);
        RateLimiter::hit($dailyKey, self::DAILY_HOURS * 3600);

        $sesi = SesiKonsultasi::create([
            'rumah_sakit_id' => $this->rs->id,
            'dokter_id'      => $dokter->id,
            'token'          => (string) Str::uuid(),
            'nama_pasien'    => $this->nama,
            'kontak_pasien'  => $this->kontak,
            'status'         => StatusSesiKonsultasi::MENUNGGU,
            'durasi_menit'   => $dokter->durasi_sesi_menit,
        ]);

        broadcast(new SesiStatusBerubah($sesi));

        Cookie::queue($this->cookieSesiKey(), $sesi->token, $dokter->durasi_sesi_menit);

        $this->redirect(route('rumahsakit.konsultasi', [
            'rumahsakit' => $this->rs->slug,
            'sesi'       => $sesi->token,
        ]));
    }

    public function render()
    {
        $dokter = Dokter::query()
            ->where('rumah_sakit_id', $this->rs->id)
            ->where('aktif', true)
            ->where('dapat_konsultasi', true)
            ->with('spesialis')
            ->orderByDesc('tersedia_konsultasi')
            ->orderBy('nama')
            ->get();

        return view('rumah_sakit.pages.tanya-dokter', ['dokter' => $dokter]);
    }
}
