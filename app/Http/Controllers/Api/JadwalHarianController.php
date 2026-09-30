<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalHarian;
use App\Models\RumahSakit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JadwalHarianController extends Controller
{
    /** Bentuk 1 baris jadwal_harian jadi payload dokter (dipakai jadwal() & jadwalBulanan()). */
    private function dokterPayload(JadwalHarian $r): array
    {
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
    }

    private function rumahSakitOr404(string $rs): RumahSakit|JsonResponse
    {
        return RumahSakit::where('slug', $rs)->first()
            ?? response()->json(['message' => 'Rumah sakit tidak ditemukan.'], 404);
    }

    public function jadwal(RumahSakit $rumahSakit, string $tanggal, bool $executive = false)
    {
        $jadwalHarian = JadwalHarian::whereDate('tanggal', $tanggal)
            ->where('is_executive', $executive)
            ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rumahSakit->id))
            ->with(['poliklinik', 'dokter', 'perubahan'])
            ->get();

        return $jadwalHarian
            ->groupBy('poliklinik_id')
            ->map(fn ($rows) => [
                'poliklinik' => $rows->first()->poliklinik->nama,
                'dokter'     => $rows->map(fn ($r) => $this->dokterPayload($r))->values(),
            ])
            ->values();
    }

    /** 1 bulan penuh — dikelompokkan per poliklinik, lalu per dokter, lalu list jadwal (tanggal+jam)-nya. */
    public function jadwalBulanan(RumahSakit $rumahSakit, int $bulan, int $tahun, bool $executive = false)
    {
        $jadwalHarian = JadwalHarian::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->where('is_executive', $executive)
            ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rumahSakit->id))
            ->with(['poliklinik', 'dokter', 'perubahan'])
            ->orderBy('tanggal')
            ->get();

        return $jadwalHarian
            ->groupBy('poliklinik_id')
            ->map(fn ($rowsPerPoli) => [
                'poliklinik' => $rowsPerPoli->first()->poliklinik->nama,
                'dokter'     => $rowsPerPoli
                    ->groupBy(fn (JadwalHarian $r) => $r->dokter_id ?? $r->nama_dokter)
                    ->map(function ($rowsPerDokter) {
                        return [
                            'nama'   => $this->dokterPayload($rowsPerDokter->first())['nama'],
                            'jadwal' => $rowsPerDokter->map(function (JadwalHarian $r) {
                                $item = $this->dokterPayload($r);
                                unset($item['nama']);
                                return ['tanggal' => $r->tanggal->format('Y-m-d')] + $item;
                            })->values(),
                        ];
                    })
                    ->values(),
            ])
            ->values();
    }

    public function index(Request $request, string $rs): JsonResponse
    {
        $rumahSakit = $this->rumahSakitOr404($rs);
        if ($rumahSakit instanceof JsonResponse) return $rumahSakit;

        $request->validate(['tanggal' => ['nullable', 'date_format:Y-m-d']]);
        $tanggal = $request->input('tanggal') ?? now()->format('Y-m-d');

        return response()->json([
            'tanggal'     => $tanggal,
            'rumah_sakit' => $rumahSakit->nama,
            'data'        => $this->jadwal($rumahSakit, $tanggal),
        ]);
    }

    public function executive(Request $request, string $rs): JsonResponse
    {
        $rumahSakit = $this->rumahSakitOr404($rs);
        if ($rumahSakit instanceof JsonResponse) return $rumahSakit;

        $request->validate(['tanggal' => ['nullable', 'date_format:Y-m-d']]);
        $tanggal = $request->input('tanggal') ?? now()->format('Y-m-d');

        return response()->json([
            'tanggal'     => $tanggal,
            'rumah_sakit' => $rumahSakit->nama,
            'data'        => $this->jadwal($rumahSakit, $tanggal, true),
        ]);
    }

    public function bulanan(Request $request, string $rs): JsonResponse
    {
        $rumahSakit = $this->rumahSakitOr404($rs);
        if ($rumahSakit instanceof JsonResponse) return $rumahSakit;

        $validated = $request->validate([
            'bulan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);
        $bulan = $validated['bulan'] ?? (int) now()->format('n');
        $tahun = $validated['tahun'] ?? (int) now()->format('Y');

        return response()->json([
            'rumah_sakit' => $rumahSakit->nama,
            'data'        => $this->jadwalBulanan($rumahSakit, $bulan, $tahun),
        ]);
    }

    public function bulananExecutive(Request $request, string $rs): JsonResponse
    {
        $rumahSakit = $this->rumahSakitOr404($rs);
        if ($rumahSakit instanceof JsonResponse) return $rumahSakit;

        $validated = $request->validate([
            'bulan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);
        $bulan = $validated['bulan'] ?? (int) now()->format('n');
        $tahun = $validated['tahun'] ?? (int) now()->format('Y');

        return response()->json([
            'rumah_sakit' => $rumahSakit->nama,
            'data'        => $this->jadwalBulanan($rumahSakit, $bulan, $tahun, true),
        ]);
    }
}
