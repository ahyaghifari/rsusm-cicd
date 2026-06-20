<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client untuk status live antrian poliklinik per dokter.
 *
 * Base URL-nya **per RS**, bukan config global — diambil dari kolom
 * `rumah_sakit.link_antrian` (sama kolom yang dipakai kartu "Pantauan Antrian"). Endpoint:
 * {link_antrian}/api/public/{nomor_poli_antrian}.
 */
class AntrianApiClient
{
    /**
     * @return array{id: mixed, nama_poli: string, nama_dokter: string, status: string}|null
     *         null kalau base URL kosong atau request gagal.
     */
    public function fetch(?string $baseUrl, int|string $nomorPoliAntrian): ?array
    {
        if (! $baseUrl) {
            return null;
        }

        $url = rtrim($baseUrl, '/') . '/api/public/poli/' . $nomorPoliAntrian;

        try {
            $response = Http::timeout(10)->get($url);
        } catch (\Throwable $e) {
            Log::warning("AntrianApiClient: gagal menghubungi {$url} — " . $e->getMessage());
            return null;
        }

        if ($response->failed()) {
            Log::warning("AntrianApiClient: respons gagal dari {$url} (HTTP {$response->status()})");
            return null;
        }

        return $response->json();
    }
}
