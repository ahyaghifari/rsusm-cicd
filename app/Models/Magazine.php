<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Magazine extends Model
{
    protected $fillable = [
        'sort_order',
        'rumah_sakit_id',
        'judul',
        'slug',
        'cover',
        'file_pdf',
        'deskripsi',
        'aktif',
        'published_at',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'published_at' => 'date',
    ];

    public function rumahSakit()
    {
        return $this->belongsTo(RumahSakit::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}

