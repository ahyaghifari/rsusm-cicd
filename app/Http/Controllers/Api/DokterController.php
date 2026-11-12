<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use Illuminate\Http\JsonResponse;

class DokterController extends Controller
{
    public function index(): JsonResponse
    {
        $dokter = Dokter::where('aktif', true)
            ->with(['spesialis:id,nama', 'rumahSakit:id,nama'])
            ->orderBy('nama')
            ->get()
            ->map(fn (Dokter $d) => [
                'nama'        => $d->nama,
                'spesialis'   => $d->spesialis?->nama,
                'rumah_sakit' => $d->rumahSakit?->nama,
            ]);

        return response()->json(['data' => $dokter]);
    }
}
