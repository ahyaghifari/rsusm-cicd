<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosterGenerate;
use App\Models\RumahSakit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class PosterController extends Controller
{
    public function jadwalHarian(Request $request, string $rs): BinaryFileResponse|JsonResponse
    {
        return $this->downloadJadwalHarian($request, $rs, 'REGULER');
    }

    public function jadwalHarianExecutive(Request $request, string $rs): BinaryFileResponse|JsonResponse
    {
        return $this->downloadJadwalHarian($request, $rs, 'EKSEKUTIF');
    }

    private function downloadJadwalHarian(Request $request, string $rs, string $kategori): BinaryFileResponse|JsonResponse
    {
        $rumahSakit = RumahSakit::where('slug', $rs)->first();

        if (! $rumahSakit) {
            return response()->json(['message' => 'Rumah sakit tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'halaman' => ['nullable', 'integer', 'min:1'],
        ]);

        $tanggal = $validated['tanggal'] ?? now()->format('Y-m-d');
        $halaman = $validated['halaman'] ?? 1;
        $poster = PosterGenerate::query()
            ->where('rumah_sakit_id', $rumahSakit->id)
            ->where('jenis', 'JADWAL_HARIAN')
            ->where('kategori_klinik', $kategori)
            ->whereDate('tanggal', $tanggal)
            ->where('halaman', $halaman)
            ->first();

        if (! $poster) {
            return response()->json([
                'message' => "Poster jadwal {$kategori} belum tersedia untuk tanggal {$tanggal}.",
            ], 404);
        }

        $path = $poster->path;

        if (! Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File poster tidak ditemukan di storage.'], 404);
        }

        return response()->file(
            Storage::disk('public')->path($path),
            [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'inline; filename="poster-jadwal-{$kategori}-{$rs}-{$tanggal}-hal{$halaman}.png"',
            ]
        );
    }
}
