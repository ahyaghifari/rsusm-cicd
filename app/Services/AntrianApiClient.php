<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client untuk status live antrian poliklinik per dokter.
 *
 * Base URL-nya **per RS**, bukan config global — diambil dari kolom
 * `rumah_sakit.link_antrian` (sama kolom yang dipakai kartu "Pantauan Antrian"). Endpoint:
 * {link_antrian}/api/public/poli/{nomor_poli_antrian}.
 *
 * Autentikasi Basic Auth — kredensialnya global (sama untuk semua RS), dari
 * config('services.antrian.username'/'password') (env ANTRIAN_API_USERNAME/PASSWORD).
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
            $response = Http::timeout(10)
                ->withBasicAuth(
                    config('services.antrian.username') ?? '',
                    config('services.antrian.password') ?? '',
                )
                ->get($url);
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
