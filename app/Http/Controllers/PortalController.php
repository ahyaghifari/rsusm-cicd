<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RumahSakit;
use App\Models\Spesialis;

class PortalController extends Controller
{
    public function index()
    {
        $rumahsakit = RumahSakit::where('aktif', true)->get();

        return view('welcome', ['rumahsakit' => $rumahsakit]);
    }

    public function spesialis(Request $request)
    {
        $rsSlug = $request->input('rs');

        $daftarSpesialis = Spesialis::whereHas('rumahsakit', function($q) use ($rsSlug) {
            $q->where('slug', $rsSlug);
        })->whereHas('dokter')->get();

        return response()->json($daftarSpesialis);
    }
}
