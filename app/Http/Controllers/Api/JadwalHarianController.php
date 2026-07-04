<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalHarian;
use App\Models\RumahSakit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JadwalHarianController extends Controller
{
    public function index(Request $request, string $rs): JsonResponse
    {
        $rumahSakit = RumahSakit::where('slug', $rs)->first();

        if (! $rumahSakit) {
            return response()->json(['message' => 'Rumah sakit tidak ditemukan.'], 404);
        }

        $request->validate([
            'tanggal' => ['required', 'date_format:Y-m-d'],
        ]);

        $tanggal = $request->input('tanggal');

        $jadwalHarian = JadwalHarian::whereDate('tanggal', $tanggal)
            ->where('is_executive', 0)
            ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rumahSakit->id))
            ->with(['poliklinik', 'dokter', 'perubahan'])
            ->get();

        $data = $jadwalHarian
            ->groupBy('poliklinik_id')
            ->map(function ($rows) {
                $poli = $rows->first()->poliklinik;

                $dokter = $rows->map(function ($r) {
                    $p = $r->perubahan;

                    $jamMulai   = ($p?->jam_mulai   ?? $r->jam_mulai)?->format('H:i');
                    $jamSelesai = ($p?->jam_selesai  ?? $r->jam_selesai)?->format('H:i') ?? 'Selesai';
                    $status     = $p?->status_layanan?->value ?? $r->status_layanan?->value ?? 'BUKA';

                    return [
                        'nama'              => $r->nama_dokter ?: ($r->dokter?->nama ?? '-'),
                        'jam_mulai'         => $jamMulai,
                        'jam_selesai'       => $jamSelesai,
                        'status'            => $status,
                        'sesuai_perjanjian' => (bool) $r->sesuai_perjanjian,
                        'catatan'           => $p?->catatan ?: ($r->catatan ?? ''),
                    ];
                })->values();

                return [
                    'poliklinik' => $poli->nama,
                    'dokter'     => $dokter,
                ];
            })
            ->values();

        return response()->json([
            'tanggal'      => $tanggal,
            'rumah_sakit'  => $rumahSakit->nama,
            'data'         => $data,
        ]);
    }
}
