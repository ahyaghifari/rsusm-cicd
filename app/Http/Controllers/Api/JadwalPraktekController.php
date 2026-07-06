<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalPraktek;
use App\Models\RumahSakit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JadwalPraktekController extends Controller
{
    private const HARI_ORDER = [
        'SENIN' => 1, 'SELASA' => 2, 'RABU' => 3, 'KAMIS' => 4,
        'JUMAT' => 5, 'SABTU' => 6, 'MINGGU' => 7,
    ];

    public function index(Request $request, string $rs): JsonResponse
    {
        $rumahSakit = RumahSakit::where('slug', $rs)->first();

        if (! $rumahSakit) {
            return response()->json(['message' => 'Rumah sakit tidak ditemukan.'], 404);
        }

        $jadwal = JadwalPraktek::where('is_executive', 0)
            ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rumahSakit->id))
            ->with('dokter')
            ->get();

        // Group by dokter, dalam dokter group by jam
        $data = $jadwal
            ->groupBy(fn ($j) => $j->dokter_id ?? ('nama:' . $j->nama_dokter))
            ->map(function ($rows) {
                $first     = $rows->first();
                $namaDokter = $first->nama_dokter ?: ($first->dokter?->nama ?? '-');

                // Group hari berdasarkan jam yang sama
                $jadwalDokter = $rows
                    ->groupBy(fn ($j) => $this->jamKey($j))
                    ->map(function ($sameJam) {
                        $first = $sameJam->first();

                        $hariLabels = $sameJam
                            ->sortBy(fn ($j) => self::HARI_ORDER[$j->hari->value] ?? 9)
                            ->map(fn ($j) => $j->hari->getLabel())
                            ->values()
                            ->all();

                        return [
                            'hari' => implode(', ', $hariLabels),
                            'jam'  => $this->formatJam($first),
                        ];
                    })
                    ->values();

                return [
                    'nama_dokter' => $namaDokter,
                    'jadwal'      => $jadwalDokter,
                ];
            })
            ->values();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    private function jamKey(JadwalPraktek $j): string
    {
        if ($j->sesuai_perjanjian) return 'perjanjian';

        return ($j->waktu_mulai?->format('H:i') ?? '') . '|' . ($j->waktu_selesai?->format('H:i') ?? '');
    }

    private function formatJam(JadwalPraktek $j): string
    {
        if ($j->sesuai_perjanjian) return 'Sesuai Perjanjian';

        $mulai   = $j->waktu_mulai?->format('H:i') ?? '?';
        $selesai = $j->waktu_selesai?->format('H:i') ?? 'Selesai';

        return "{$mulai} - {$selesai}";
    }
}
