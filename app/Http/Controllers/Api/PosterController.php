<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RumahSakit;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class PosterController extends Controller
{
    public function jadwalHarian(string $rs): BinaryFileResponse|JsonResponse
    {
        $rumahSakit = RumahSakit::where('slug', $rs)->first();

        if (! $rumahSakit) {
            return response()->json(['message' => 'Rumah sakit tidak ditemukan.'], 404);
        }

        $path = "generated-poster/default.png";

        if (! Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'Poster jadwal harian belum tersedia untuk rumah sakit ini.'], 404);
        }

        return response()->file(Storage::disk('public')->path($path));
    }
}
