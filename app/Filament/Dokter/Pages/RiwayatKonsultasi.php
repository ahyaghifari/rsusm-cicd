<?php

namespace App\Filament\Dokter\Pages;

use App\Enums\StatusSesiKonsultasi;
use App\Models\Dokter;
use App\Models\SesiKonsultasi;
use Filament\Pages\Page;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

class RiwayatKonsultasi extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon  = 'heroicon-o-clock';
    protected static ?string $title           = 'Riwayat Konsultasi';
    protected static ?string $navigationLabel = 'Riwayat';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.dokter.pages.riwayat-konsultasi';

    #[Locked]
    public ?Dokter $dokter = null;

    public string $search       = '';
    public ?int   $detailSesiId = null;

    public bool   $editingKesimpulan = false;
    public string $kesimpulanEdit    = '';

    public function mount(): void
    {
        $this->dokter = Dokter::where('user_id', filament()->auth()->id())->first();

        abort_if(! $this->dokter, 403, 'Akun Anda belum terhubung dengan data dokter manapun.');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function lihatDetail(int $id): void
    {
        if ($this->detailSesiId === $id) {
            $this->detailSesiId      = null;
            $this->editingKesimpulan = false;
            $this->kesimpulanEdit    = '';
            return;
        }

        $this->detailSesiId      = $id;
        $this->editingKesimpulan = false;
        $this->kesimpulanEdit    = '';
    }

    public function tutupDetail(): void
    {
        $this->detailSesiId      = null;
        $this->editingKesimpulan = false;
        $this->kesimpulanEdit    = '';
    }

    public function mulaiEditKesimpulan(): void
    {
        $sesi = SesiKonsultasi::find($this->detailSesiId);
        $this->kesimpulanEdit    = $sesi?->kesimpulan ?? '';
        $this->editingKesimpulan = true;
    }

    public function simpanKesimpulan(): void
    {
        $sesi = SesiKonsultasi::where('dokter_id', $this->dokter->id)->findOrFail($this->detailSesiId);
        $sesi->update(['kesimpulan' => trim($this->kesimpulanEdit) ?: null]);

        $this->editingKesimpulan = false;
        $this->kesimpulanEdit    = '';
    }

    public function batalEditKesimpulan(): void
    {
        $this->editingKesimpulan = false;
        $this->kesimpulanEdit    = '';
    }

    protected function getViewData(): array
    {
        $sesiList = SesiKonsultasi::query()
            ->where('dokter_id', $this->dokter->id)
            ->whereIn('status', [StatusSesiKonsultasi::SELESAI, StatusSesiKonsultasi::KEDALUWARSA])
            ->when($this->search, fn ($q) => $q->where('nama_pasien', 'like', '%' . $this->search . '%'))
            ->withCount('pesan')
            ->orderByDesc('created_at')
            ->paginate(15);

        $detailSesi = $this->detailSesiId
            ? SesiKonsultasi::with('pesan')->find($this->detailSesiId)
            : null;

        return [
            'sesiList'   => $sesiList,
            'detailSesi' => $detailSesi,
        ];
    }
}
