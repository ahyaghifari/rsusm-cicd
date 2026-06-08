<?php

namespace App\Console\Commands;

use App\Enums\Hari;
use App\Models\JadwalHarian;
use App\Models\JadwalPraktek;
use App\Models\RumahSakit;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateJadwalHarian extends Command
{
    protected $signature = 'jadwal:generate-harian {tanggal? : Tanggal target format Y-m-d, default hari ini}';

    protected $description = 'Generate JadwalHarian dari JadwalPraktek untuk tanggal tertentu. Skip jika sudah ada.';

    public function handle(): int
    {
        $tanggalInput = $this->argument('tanggal');

        try {
            $tanggal = $tanggalInput
                ? Carbon::createFromFormat('Y-m-d', $tanggalInput)->startOfDay()
                : Carbon::today();
        } catch (\Exception) {
            $this->error("Format tanggal tidak valid: {$tanggalInput}. Gunakan format Y-m-d.");
            return self::FAILURE;
        }

        $tanggalStr = $tanggal->format('Y-m-d');
        $hariValue  = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'][$tanggal->dayOfWeek];
        $hariLabel  = Hari::from($hariValue)->getLabel();

        $this->info("Generating JadwalHarian untuk: {$tanggalStr} ({$hariLabel})");
        $this->newLine();

        $rumahSakits = RumahSakit::where('aktif', true)->get();

        if ($rumahSakits->isEmpty()) {
            $this->warn('Tidak ada Rumah Sakit aktif ditemukan.');
            return self::SUCCESS;
        }

        $totalInsert = 0;
        $totalSkip   = 0;

        foreach ($rumahSakits as $rs) {
            $inserted = 0;
            $skipped  = 0;

            try {
                DB::transaction(function () use ($rs, $hariValue, $tanggalStr, &$inserted, &$skipped) {

                    // Ambil semua JadwalPraktek untuk RS ini pada hari yang sesuai
                    $jadwalPrakteks = JadwalPraktek::where('hari', $hariValue)
                        ->whereHas('poliklinik', function ($q) use ($rs) {
                            $q->where('rumah_sakit_id', $rs->id)
                              ->where('aktif', true);
                        })
                        ->with('poliklinik')
                        ->get();

                    foreach ($jadwalPrakteks as $jp) {
                        // Cek per (tanggal, poliklinik_id) — granular, bukan per RS
                        $sudahAda = JadwalHarian::where('tanggal', $tanggalStr)
                            ->where('poliklinik_id', $jp->poliklinik_id)
                            ->exists();

                        if ($sudahAda) {
                            $skipped++;
                            continue;
                        }

                        JadwalHarian::create([
                            'poliklinik_id'  => $jp->poliklinik_id,
                            'tanggal'        => $tanggalStr,
                            'dokter_id'      => $jp->dokter_id,
                            'nama_dokter'    => $jp->nama_dokter,
                            'jam_mulai'      => $jp->waktu_mulai?->format('H:i'),
                            'jam_selesai'    => $jp->waktu_selesai?->format('H:i'),
                            'status_layanan' => 'BUKA',
                            'catatan'        => $jp->catatan,
                            'is_executive'   => $jp->is_executive,
                            'sumber'         => 'GENERATE',
                        ]);

                        $inserted++;
                    }
                });

                if ($inserted > 0 || $skipped > 0) {
                    $this->line("  {$rs->nama}: <fg=green>{$inserted} insert</> | <fg=yellow>{$skipped} skip</>");
                }

            } catch (\Exception $e) {
                $this->error("  {$rs->nama}: GAGAL — {$e->getMessage()}");
                Log::error("GenerateJadwalHarian gagal untuk RS {$rs->id} ({$rs->nama})", [
                    'tanggal' => $tanggalStr,
                    'error'   => $e->getMessage(),
                ]);
            }

            $totalInsert += $inserted;
            $totalSkip   += $skipped;
        }

        $this->newLine();
        $this->info("Selesai: {$totalInsert} baris di-insert, {$totalSkip} baris di-skip.");

        Log::info("GenerateJadwalHarian selesai", [
            'tanggal' => $tanggalStr,
            'hari'    => $hariLabel,
            'insert'  => $totalInsert,
            'skip'    => $totalSkip,
        ]);

        return self::SUCCESS;
    }
}
