<?php

namespace App\Console\Commands;

use App\Models\KelasRawatInap;
use App\Models\RawatInapKetersediaan;
use App\Services\RanapApiClient;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncKetersediaanRawatInap extends Command
{
    protected $signature = 'rawat-inap:sync-ketersediaan';

    protected $description = 'Sync data ketersediaan kamar rawat inap dari sistem Ranap ke cache lokal.';

    public function handle(RanapApiClient $client): int
    {
        $rumahSakitId = config('services.ranap.rumah_sakit_id');

        if (! $rumahSakitId) {
            $this->error('RANAP_RUMAH_SAKIT_ID belum dikonfigurasi di .env.');
            return self::FAILURE;
        }

        $records = $client->fetch();

        if (empty($records)) {
            $this->warn('Tidak ada data diterima dari sistem Ranap.');
            return self::SUCCESS;
        }

        $kelasByApiId = KelasRawatInap::where('rumah_sakit_id', $rumahSakitId)
            ->whereNotNull('id_kelas_api')
            ->pluck('id', 'id_kelas_api');

        $now = Carbon::now();
        $count = 0;

        foreach ($records as $record) {
            RawatInapKetersediaan::updateOrCreate(
                [
                    'rumah_sakit_id' => $rumahSakitId,
                    'external_id'    => $record['id'],
                ],
                [
                    'ruang_kamar'         => $record['ruangKamar'],
                    'tempat_tidur'        => $record['tempatTidur'],
                    'status'              => $record['status'],
                    'tanggal_update_api'  => $record['tanggal'] ?? null,
                    'keterangan'          => $record['keterangan'] ?? null,
                    'ruangan'             => $record['ruangan'],
                    'nama_kamar'          => $record['namaKamar'],
                    'kelas_rawat_inap_id' => $kelasByApiId[$record['idKelas'] ?? null] ?? null,
                    'synced_at'           => $now,
                ]
            );
            $count++;
        }

        $this->info("Sync selesai: {$count} record diperbarui.");

        return self::SUCCESS;
    }
}
