<?php

namespace App\Http\Controllers\Api;

use App\Enums\Hari;
use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\JadwalHarian;
use App\Models\JadwalPraktek;
use App\Models\RumahSakit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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
            'kode'              => $r->dokter?->kode,
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

    /**
     * Jadwal praktek master (mingguan) — dikelompokkan per poliklinik, lalu per dokter,
     * lalu list hari+jam prakteknya. Sumbernya jadwal_praktek (pola tetap), bukan
     * jadwal_harian (instance per tanggal) — jadi tidak terikat bulan/tahun tertentu.
     */
    public function jadwalBulanan(RumahSakit $rumahSakit, bool $executive = false)
    {
        $urutanHari = Hari::cases();

        $jadwalPraktek = JadwalPraktek::where('is_executive', $executive)
            ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rumahSakit->id))
            ->with(['poliklinik', 'dokter'])
            ->get()
            ->sortBy(fn (JadwalPraktek $r) => array_search($r->hari, $urutanHari, true));

        return $jadwalPraktek
            ->groupBy('poliklinik_id')
            ->map(fn ($rowsPerPoli) => [
                'poliklinik' => $rowsPerPoli->first()->poliklinik->nama,
                'dokter'     => $rowsPerPoli
                    ->groupBy(fn (JadwalPraktek $r) => $r->dokter_id ?? $r->nama_dokter)
                    ->map(fn ($rowsPerDokter) => [
                        'kode'   => $rowsPerDokter->first()->dokter?->kode,
                        'nama'   => $rowsPerDokter->first()->nama_dokter ?: ($rowsPerDokter->first()->dokter?->nama ?? '-'),
                        'jadwal' => $rowsPerDokter->map(fn (JadwalPraktek $r) => [
                            'hari'              => $r->hari->value,
                            'jam_mulai'         => $r->waktu_mulai?->format('H:i'),
                            'jam_selesai'       => $r->waktu_selesai?->format('H:i') ?? 'Selesai',
                            'sesuai_perjanjian' => (bool) $r->sesuai_perjanjian,
                            'catatan'           => $r->catatan ?? '',
                        ])->values(),
                    ])
                    ->values(),
            ])
            ->values();
    }

    /** Jadwal harian reguler + executive digabung, dikelompokkan per dokter. */
    public function perDokter(Request $request, string $rs): JsonResponse
    {
        $rumahSakit = $this->rumahSakitOr404($rs);
        if ($rumahSakit instanceof JsonResponse) return $rumahSakit;

        $request->validate(['tanggal' => ['nullable', 'date_format:Y-m-d']]);
        $tanggal = $request->input('tanggal') ?? now()->format('Y-m-d');

        $data = JadwalHarian::whereDate('tanggal', $tanggal)
            ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rumahSakit->id))
            ->with(['poliklinik', 'dokter', 'perubahan'])
            ->get()
            ->groupBy(fn (JadwalHarian $r) => $r->dokter_id ?? $r->nama_dokter)
            ->map(function ($rows) {
                $p = $this->dokterPayload($rows->first());

                return [
                    'kode'   => $p['kode'],
                    'nama'   => $p['nama'],
                    'jadwal' => $rows->sortBy('jam_mulai')->map(fn (JadwalHarian $r) => [
                        'poliklinik' => $r->poliklinik->nama,
                        'jenis'      => $r->is_executive ? 'EXECUTIVE' : 'REGULER',
                    ] + Arr::except($this->dokterPayload($r), ['kode', 'nama']))->values(),
                ];
            })
            ->sortBy('nama')
            ->values();

        return response()->json([
            'tanggal'     => $tanggal,
            'rumah_sakit' => $rumahSakit->nama,
            'data'        => $data,
        ]);
    }

    /**
     * Jadwal satu dokter (dicari lewat kode): `harian` = jadwal tanggal tsb (default hari ini),
     * `bulanan` = jadwal praktek master mingguan. Reguler + executive digabung (field `jenis`).
     */
    public function dokter(Request $request, string $rs, string $kode): JsonResponse
    {
        $rumahSakit = $this->rumahSakitOr404($rs);
        if ($rumahSakit instanceof JsonResponse) return $rumahSakit;

        $dokter = Dokter::where('rumah_sakit_id', $rumahSakit->id)->where('kode', strtoupper($kode))->first();
        if (! $dokter) {
            return response()->json(['message' => 'Dokter tidak ditemukan.'], 404);
        }

        $request->validate(['tanggal' => ['nullable', 'date_format:Y-m-d']]);
        $tanggal = $request->input('tanggal') ?? now()->format('Y-m-d');
        $jenis   = fn ($r) => $r->is_executive ? 'EXECUTIVE' : 'REGULER';

        $harian = JadwalHarian::whereDate('tanggal', $tanggal)->where('dokter_id', $dokter->id)
            ->with(['poliklinik', 'dokter', 'perubahan'])->get()->sortBy('jam_mulai')
            ->map(fn (JadwalHarian $r) => [
                'poliklinik' => $r->poliklinik->nama,
                'jenis'      => $jenis($r),
            ] + Arr::except($this->dokterPayload($r), ['kode', 'nama']))
            ->values();

        $urutanHari = Hari::cases();
        $bulanan = JadwalPraktek::where('dokter_id', $dokter->id)->with('poliklinik')->get()
            ->sortBy(fn (JadwalPraktek $r) => array_search($r->hari, $urutanHari, true))
            ->map(fn (JadwalPraktek $r) => [
                'poliklinik'        => $r->poliklinik->nama,
                'jenis'             => $jenis($r),
                'hari'              => $r->hari->value,
                'jam_mulai'         => $r->waktu_mulai?->format('H:i'),
                'jam_selesai'       => $r->waktu_selesai?->format('H:i') ?? 'Selesai',
                'sesuai_perjanjian' => (bool) $r->sesuai_perjanjian,
                'catatan'           => $r->catatan ?? '',
            ])
            ->values();

        return response()->json([
            'rumah_sakit' => $rumahSakit->nama,
            'dokter'      => ['kode' => $dokter->kode, 'nama' => $dokter->nama],
            'tanggal'     => $tanggal,
            'harian'      => $harian,
            'bulanan'     => $bulanan,
        ]);
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

    public function bulanan(string $rs): JsonResponse
    {
        $rumahSakit = $this->rumahSakitOr404($rs);
        if ($rumahSakit instanceof JsonResponse) return $rumahSakit;

        return response()->json([
            'rumah_sakit' => $rumahSakit->nama,
            'data'        => $this->jadwalBulanan($rumahSakit),
        ]);
    }

    public function bulananExecutive(string $rs): JsonResponse
    {
        $rumahSakit = $this->rumahSakitOr404($rs);
        if ($rumahSakit instanceof JsonResponse) return $rumahSakit;

        return response()->json([
            'rumah_sakit' => $rumahSakit->nama,
            'data'        => $this->jadwalBulanan($rumahSakit, true),
        ]);
    }
}
