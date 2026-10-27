<?php

namespace App\Console\Commands;

use App\Models\RumahSakit;
use App\Models\Spesialis;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ExportSpesialisCsv extends Command
{
    protected $signature = 'spesialis:export-csv
        {rumah_sakit : Nama rumah sakit (boleh sebagian)}
        {--output= : Path file CSV, default storage/app/spesialis-<slug-rs>.csv}';

    protected $description = 'Export spesialis satu rumah sakit (dicari berdasarkan nama) ke CSV.';

    public function handle(): int
    {
        $nama = $this->argument('rumah_sakit');
        $cocok = RumahSakit::where('nama', 'like', "%{$nama}%")->get();

        if ($cocok->isEmpty()) {
            $this->error("Rumah sakit '{$nama}' tidak ditemukan.");
            return self::FAILURE;
        }

        // ponytail: nama harus tepat satu hasil, tidak ada pilihan interaktif
        if ($cocok->count() > 1) {
            $this->error("'{$nama}' cocok dengan lebih dari satu rumah sakit: " . $cocok->pluck('nama')->implode(', '));
            return self::FAILURE;
        }

        $rs   = $cocok->first();
        $path = $this->option('output') ?: storage_path('app/spesialis-' . Str::slug($rs->nama) . '.csv');

        $fh = fopen($path, 'w');
        if (! $fh) {
            $this->error("Tidak bisa menulis ke {$path}.");
            return self::FAILURE;
        }

        fwrite($fh, "\xEF\xBB\xBF"); // BOM supaya Excel baca UTF-8
        fputcsv($fh, ['kode', 'nama', 'slug', 'aktif']);

        $total = 0;
        Spesialis::where('rumah_sakit_id', $rs->id)->orderBy('nama')->each(function (Spesialis $s) use ($fh, &$total) {
            fputcsv($fh, [$s->kode, $s->nama, $s->slug, $s->aktif ? 1 : 0]);
            $total++;
        });

        fclose($fh);
        $this->info("{$total} spesialis {$rs->nama} diexport ke {$path}");

        return self::SUCCESS;
    }
}
