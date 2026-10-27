<?php

namespace App\Console\Commands;

use App\Models\Dokter;
use App\Models\RumahSakit;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ExportDokterCsv extends Command
{
    protected $signature = 'dokter:export-csv
        {rumah_sakit : Nama rumah sakit (boleh sebagian)}
        {--output= : Path file CSV, default storage/app/dokter-<slug-rs>.csv}';

    protected $description = 'Export dokter (kode, nama, foto, spesialis + kode spesialis) satu rumah sakit ke CSV.';

    public function handle(): int
    {
        $nama = $this->argument('rumah_sakit');
        $cocok = RumahSakit::where('nama', 'like', "%{$nama}%")->get();

        if ($cocok->isEmpty()) {
            $this->error("Rumah sakit '{$nama}' tidak ditemukan.");
            return self::FAILURE;
        }

        if ($cocok->count() > 1) {
            $this->error("'{$nama}' cocok dengan lebih dari satu rumah sakit: " . $cocok->pluck('nama')->implode(', '));
            return self::FAILURE;
        }

        $rs   = $cocok->first();
        $path = $this->option('output') ?: storage_path('app/dokter-' . Str::slug($rs->nama) . '.csv');

        $fh = fopen($path, 'w');
        if (! $fh) {
            $this->error("Tidak bisa menulis ke {$path}.");
            return self::FAILURE;
        }

        fwrite($fh, "\xEF\xBB\xBF"); // BOM supaya Excel baca UTF-8
        fputcsv($fh, ['kode', 'nama', 'foto', 'spesialis', 'kode_spesialis']);

        $total = 0;
        Dokter::with('spesialis')->where('rumah_sakit_id', $rs->id)->orderBy('nama')
            ->each(function (Dokter $d) use ($fh, &$total) {
                fputcsv($fh, [
                    $d->kode,
                    $d->nama,
                    $d->foto ? asset('storage/' . $d->foto) : '',
                    $d->spesialis?->nama ?? 'Umum',
                    $d->spesialis?->kode,
                ]);
                $total++;
            });

        fclose($fh);
        $this->info("{$total} dokter {$rs->nama} diexport ke {$path}");

        return self::SUCCESS;
    }
}
