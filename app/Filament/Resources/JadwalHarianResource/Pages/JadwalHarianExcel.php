<?php

namespace App\Filament\Resources\JadwalHarianResource\Pages;

use App\Models\JadwalHarian;
use App\Models\PoliKlinik;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class JadwalHarianExcel extends JadwalHarianPage
{
    protected static string $view = 'filament.resources.jadwal-harian-resource.pages.jadwal-harian-excel';

    protected static ?string $title = 'Jadwal Harian (Excel)';

    protected static ?string $navigationLabel = 'Jadwal Harian · Excel';

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 3;

    // =========================================================================
    // OVERRIDE — dispatch gridRows setelah data berubah
    // =========================================================================

    public function setActiveTanggal(string $tanggal): void
    {
        parent::setActiveTanggal($tanggal);
        $this->dispatch('gridRows', rows: $this->rows);
    }

    public function updatedSelectedRumahSakitId(): void
    {
        parent::updatedSelectedRumahSakitId();
        $this->dispatch('gridRows', rows: $this->rows);
    }

    public function updatedSelectedUnitLayananId(): void
    {
        parent::updatedSelectedUnitLayananId();
        $this->dispatch('gridRows', rows: $this->rows);
    }

    public function muatDariJadwalMingguan(): void
    {
        parent::muatDariJadwalMingguan();
        $this->dispatch('gridRows', rows: $this->rows);
    }

    public function resetJadwal(): void
    {
        parent::resetJadwal();
        $this->dispatch('gridRows', rows: $this->rows);
    }

    // =========================================================================
    // SAVE — menerima rows dari AG Grid
    // =========================================================================

    public function saveFromGrid(array $rows): void
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) {
            Notification::make()->title('Rumah sakit tidak teridentifikasi')->danger()->send();
            return;
        }

        foreach ($rows as $i => $row) {
            $nomor = $i + 1;
            if (empty($row['poliklinik_id'])) {
                Notification::make()
                    ->title("Baris ke-{$nomor} belum lengkap")
                    ->body('Pilih poliklinik atau hapus baris tersebut.')
                    ->warning()->send();
                return;
            }
            if (empty($row['jam_mulai'])) {
                Notification::make()->title("Baris ke-{$nomor}: Jam Mulai wajib diisi")->danger()->send();
                return;
            }
            if (empty($row['status_layanan'])) {
                Notification::make()->title("Baris ke-{$nomor}: Status wajib diisi")->danger()->send();
                return;
            }
        }

        $poliIds = PoliKlinik::whereHas('unitLayanan', function ($q) use ($rsId) {
            $q->where('rumah_sakit_id', $rsId);
            if ($this->selectedUnitLayananId) {
                $q->where('id', $this->selectedUnitLayananId);
            }
        })->pluck('id')->toArray();

        DB::transaction(function () use ($rows, $poliIds) {
            JadwalHarian::where('tanggal', $this->activeTanggal)
                ->whereIn('poliklinik_id', $poliIds)
                ->delete();

            foreach ($rows as $row) {
                JadwalHarian::create([
                    'poliklinik_id'  => $row['poliklinik_id'],
                    'tanggal'        => $this->activeTanggal,
                    'dokter_id'      => $row['dokter_id'] ?: null,
                    'nama_dokter'    => $row['nama_dokter'] ?: null,
                    'jam_mulai'      => $row['jam_mulai'],
                    'jam_selesai'    => $row['jam_selesai'] ?: null,
                    'status_layanan' => $row['status_layanan'],
                    'catatan'        => $row['catatan'] ?: null,
                ]);
            }
        });

        $this->rowsCache[$this->activeTanggal] = null;
        $this->loadRows();
        $this->dispatch('gridRows', rows: $this->rows);

        $tanggalFormatted = Carbon::parse($this->activeTanggal)->translatedFormat('d F Y');
        Notification::make()
            ->title("Jadwal {$this->getNamaHariAktif()}, {$tanggalFormatted} berhasil disimpan")
            ->success()->send();
    }
}
