<?php
$rsId = App\Models\RumahSakit::first()->id;
$tanggal = now()->format('Y-m-d');
$total = App\Models\JadwalHarian::whereDate('tanggal', $tanggal)->count();
$exec1 = App\Models\JadwalHarian::whereDate('tanggal', $tanggal)->where('is_executive', 1)->count();
$exec0 = App\Models\JadwalHarian::whereDate('tanggal', $tanggal)->where('is_executive', 0)->count();
$execNull = App\Models\JadwalHarian::whereDate('tanggal', $tanggal)->whereNull('is_executive')->count();
echo "Total: $total\n";
echo "Exec 1: $exec1\n";
echo "Exec 0: $exec0\n";
echo "Exec Null: $execNull\n";
