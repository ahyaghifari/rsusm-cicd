<?php

namespace App\Events;

use App\Models\SesiKonsultasi;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class SesiStatusBerubah implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public SesiKonsultasi $sesi) {}

    /**
     * Disiarkan ke dua channel: channel publik berbasis token (untuk halaman
     * pasien yang sedang membuka sesi ini) dan private channel dokter (untuk
     * dashboard KonsultasiDashboard agar antrean ter-update tanpa refresh —
     * lihat routes/channels.php untuk otorisasinya).
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('konsultasi.' . $this->sesi->token),
            new PrivateChannel('konsultasi.dokter.' . $this->sesi->dokter_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'SesiStatusBerubah';
    }

    public function broadcastWith(): array
    {
        return [
            'sesi_id'     => $this->sesi->id,
            'token'       => $this->sesi->token,
            'status'      => $this->sesi->status->value,
            'mulai_at'    => $this->sesi->mulai_at?->toIso8601String(),
            'berakhir_at' => $this->sesi->berakhir_at?->toIso8601String(),
        ];
    }
}
