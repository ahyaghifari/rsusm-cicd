<?php

namespace App\Events;

use App\Models\KonsultasiPesan;
use App\Models\SesiKonsultasi;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class PesanDikirim implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public SesiKonsultasi $sesi,
        public KonsultasiPesan $pesan,
    ) {}

    /**
     * Channel publik berbasis token — lihat catatan keamanan di issues/tanya-dokter-plan.md
     * (tidak perlu didaftarkan di routes/channels.php karena bukan private channel).
     */
    public function broadcastOn(): array
    {
        return [new Channel('konsultasi.' . $this->sesi->token)];
    }

    public function broadcastAs(): string
    {
        return 'PesanDikirim';
    }

    /**
     * Payload diringkas manual (bukan model penuh) — cukup untuk merender satu
     * bubble chat di sisi penerima, tanpa membocorkan kolom lain dari relasi.
     */
    public function broadcastWith(): array
    {
        return [
            'id'         => $this->pesan->id,
            'pengirim'   => $this->pesan->pengirim->value,
            'isi'        => $this->pesan->isi,
            'created_at' => $this->pesan->created_at->toIso8601String(),
        ];
    }
}
