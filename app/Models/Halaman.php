<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Halaman extends Model
{
    protected $table = 'halaman';

    protected $fillable = [
        'rumah_sakit_id',
        'slug',
        'judul',
        'konten',
        'kata_kunci',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function rumahSakit()
    {
        return $this->belongsTo(RumahSakit::class);
    }
}
