<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client untuk data ketersediaan kamar dari sistem Ranap.
 *
 * Tiap RS punya identifier sendiri di URL API ({base_url}/{kode}/bed, kolom
 * rumah_sakit.ranap_kode_api). Kalau $kodeApi kosong (RS belum terhubung ke Ranap), fetch()
 * fallback membaca fixture JSON lokal supaya halaman tetap bisa di-demo dengan data contoh.
 */
class RanapApiClient
{
    /**
     * @return array<int, array{
     *     id: int, ruangKamar: int, tempatTidur: string, status: int, tanggal: string,
     *     keterangan: ?string, ruangan: string, namaKamar: string, idKelas: ?int
     * }>
     */
    public function fetch(?string $kodeApi = null): array
    {
        $baseUrl = config('services.ranap.base_url');

        if ($baseUrl && $kodeApi) {
            $url = rtrim($baseUrl, '/') . '/' . trim($kodeApi, '/') . '/bed';

            return Http::timeout(10)->get($url)->throw()->json() ?? [];
        }

        $path = storage_path(config('services.ranap.mock_path'));

        if (! file_exists($path)) {
            Log::warning("RanapApiClient: fixture tidak ditemukan di {$path}, ketersediaan rawat inap akan tampil kosong.");
            return [];
        }

        return json_decode(file_get_contents($path), true) ?? [];
    }
}
