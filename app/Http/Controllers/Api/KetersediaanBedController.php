<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatusKetersediaanKamar;
use App\Http\Controllers\Controller;
use App\Models\KelasRawatInap;
use App\Models\RumahSakit;
use App\Services\RanapApiClient;
use Illuminate\Http\JsonResponse;

class KetersediaanBedController extends Controller
{
    // ponytail: aturan filter (status 0, kelas tak dikenal/non-public) menyalin
    // Livewire KetersediaanRawatInap; ekstrak ke service bersama kalau aturannya berubah.
    public function index(string $rs): JsonResponse
    {
        $rumahSakit = RumahSakit::where('slug', $rs)->first();
        if (! $rumahSakit) {
            return response()->json(['message' => 'Rumah sakit tidak ditemukan.'], 404);
        }

        $kelasByApiId = KelasRawatInap::where('rumah_sakit_id', $rumahSakit->id)
            ->where('public', true)
            ->whereNotNull('id_kelas_api')
            ->get()
            ->keyBy('id_kelas_api');

        try {
            $beds = collect(app(RanapApiClient::class)->fetch($rumahSakit->ranap_kode_api));
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Sistem Ranap tidak dapat dihubungi.'], 502);
        }

        $beds = $beds
            ->reject(fn ($r) => $r['status'] === 0 || ! $kelasByApiId->has($r['idKelas'] ?? null))
            ->groupBy('idKelas');

        $ringkasan = fn ($rows) => collect(StatusKetersediaanKamar::cases())
            ->mapWithKeys(fn ($s) => [strtolower($s->name) => $rows->where('status', $s->value)->count()])
            ->all();

        return response()->json([
            'rumah_sakit' => $rumahSakit->nama,
            'diambil_pada' => now()->toIso8601String(),
            'ringkasan'   => $ringkasan($beds->flatten(1)),
            'data'        => $beds->map(fn ($rows, $idKelas) => [
                'kelas'     => $kelasByApiId[$idKelas]->nama,
                'ringkasan' => $ringkasan($rows),
                'kamar'     => $rows->groupBy('namaKamar')->sortKeys()->map(fn ($bedRows, $namaKamar) => [
                    'nama_kamar' => $namaKamar,
                    'bed'        => $bedRows->sortBy('tempatTidur')->map(fn ($r) => [
                        'tempat_tidur' => $r['tempatTidur'],
                        'status'       => StatusKetersediaanKamar::labelFor($r['status']),
                        'keterangan'   => $r['keterangan'] ?? null,
                        'tanggal'      => $r['tanggal'] ?? null,
                    ])->values(),
                ])->values(),
            ])->sortBy('kelas')->values(),
        ]);
    }
}
