<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\JsonResponse;

class PromoController extends Controller
{
    public function index(): JsonResponse
    {
        $promo = Promo::aktif()
            ->with('rumahSakit:id,nama')
            ->latest()
            ->get()
            ->map(fn (Promo $p) => [
                'judul'       => $p->judul,
                'gambar'      => $p->gambar ? asset('storage/' . $p->gambar) : null,
                'deskripsi'   => $p->deskripsi,
                'rumah_sakit' => $p->rumahSakit?->nama,
            ])
            ->values();

        return response()->json(['data' => $promo]);
    }
}
