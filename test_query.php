<?php
$rsId = App\Models\RumahSakit::first()->id;
$tanggal = now()->format('Y-m-d');
$filter = 'reguler_dan_eksekutif';
$polikliniks = App\Models\PoliKlinik::where('rumah_sakit_id', $rsId)->where('aktif', true)->whereHas('jadwalHarian', function ($q) use ($tanggal, $filter) {
    $q->whereDate('tanggal', $tanggal)
      ->when($filter === 'reguler', fn ($q) => $q->where('is_executive', 0))
      ->when($filter === 'eksekutif', fn ($q) => $q->where('is_executive', 1));
})->get();
echo 'Count: ' . $polikliniks->count() . PHP_EOL;
