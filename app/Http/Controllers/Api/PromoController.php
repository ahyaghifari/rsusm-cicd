<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 0);
        $limit = $limit > 0 ? min($limit, 100) : null;

        $promo = Promo::aktif()
            ->with('rumahSakit:id,nama')
            ->latest()
            ->when($limit, fn ($q) => $q->limit($limit))
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
