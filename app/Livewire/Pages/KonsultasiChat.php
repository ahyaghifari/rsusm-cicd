<?php

namespace App\Livewire\Pages;

use App\Enums\PengirimPesan;
use App\Enums\StatusSesiKonsultasi;
use App\Events\PesanDikirim;
use App\Livewire\RsPortalComponent;
use App\Models\SesiKonsultasi;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class KonsultasiChat extends RsPortalComponent
{
    #[Locked]
    public SesiKonsultasi $sesi;

    #[Locked]
    public array $riwayat = [];

    #[Validate('required|string|max:1000')]
    public string $pesanBaru = '';

    public function mount(SesiKonsultasi $sesi): void
    {
        abort_if($sesi->rumah_sakit_id !== $this->rs->id, 404);

        $this->sesi = $sesi->load('dokter');
        $this->riwayat = $sesi->pesan()
            ->orderBy('created_at')
            ->get()
            ->map(fn ($p) => [
                'id'         => $p->id,
                'pengirim'   => $p->pengirim->value,
                'isi'        => $p->isi,
                'created_at' => $p->created_at->toIso8601String(),
            ])
            ->toArray();

        $this->seo('Konsultasi - ' . $this->sesi->dokter->nama);
    }

    #[On('echo:konsultasi.{sesi.token},PesanDikirim')]
    public function pesanMasuk(array $payload): void
    {
        $this->riwayat[] = $payload;
    }

    #[On('echo:konsultasi.{sesi.token},SesiStatusBerubah')]
    public function statusBerubah(): void
    {
        $this->sesi->refresh();

        if (in_array($this->sesi->status, [StatusSesiKonsultasi::SELESAI, StatusSesiKonsultasi::KEDALUWARSA])) {
            $this->dispatch('sesi-berakhir');
        }
    }

    public function simpanPushSubscription(string $json): void
    {
        $this->sesi->update(['push_subscription' => $json]);
    }

    private const MSG_LIMIT   = 20;  // maks. pesan dalam 1 menit
    private const MSG_SECONDS = 60;

    public function kirim(): void
    {
        $this->validate();

        abort_unless($this->sesi->status === StatusSesiKonsultasi::BERLANGSUNG, 403);

        $key = 'chat-pesan:' . $this->sesi->token;

        if (RateLimiter::tooManyAttempts($key, self::MSG_LIMIT)) {
            $tunggu = RateLimiter::availableIn($key);
            $this->addError('pesanBaru', "Terlalu banyak pesan. Tunggu {$tunggu} detik sebelum mengirim lagi.");
            return;
        }

        RateLimiter::hit($key, self::MSG_SECONDS);

        $pesan = $this->sesi->pesan()->create([
            'pengirim' => PengirimPesan::PASIEN,
            'isi'      => $this->pesanBaru,
        ]);

        $payload = [
            'id'         => $pesan->id,
            'pengirim'   => $pesan->pengirim->value,
            'isi'        => $pesan->isi,
            'created_at' => $pesan->created_at->toIso8601String(),
        ];

        $this->riwayat[] = $payload;
        $this->reset('pesanBaru');

        broadcast(new PesanDikirim($this->sesi, $pesan))->toOthers();
    }

    public function render()
    {
        return view('rumah_sakit.pages.konsultasi-chat');
    }
}
