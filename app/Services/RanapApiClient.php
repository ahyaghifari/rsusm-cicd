<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Client untuk data ketersediaan kamar dari sistem Ranap.
 *
 * Belum ada endpoint/autentikasi resmi dari sistem Ranap (lihat
 * issues/ketersediaan-rawat-inap-plan.md, Keputusan #1). Untuk sekarang fetch() membaca
 * fixture JSON lokal. Saat endpoint asli + auth sudah tersedia, ganti isi method ini jadi
 * HTTP call — pemanggil (command sync) tidak perlu berubah.
 */
class RanapApiClient
{
    /**
     * @return array<int, array{
     *     id: int, ruangKamar: int, tempatTidur: string, status: int, tanggal: string,
     *     keterangan: ?string, ruangan: string, namaKamar: string, idKelas: ?int
     * }>
     */
    public function fetch(): array
    {
        $url = config('services.ranap.url');

        if ($url) {
            return Http::timeout(10)->get($url)->throw()->json() ?? [];
        }

        $path = storage_path(config('services.ranap.mock_path'));

        if (! file_exists($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true) ?? [];
    }
}
