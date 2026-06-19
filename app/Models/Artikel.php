<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Artikel extends Model
{
    use SoftDeletes;
    protected $table = 'artikel';

    protected $fillable = [
        'rumah_sakit_id', 'kategori_artikel_id', 'judul', 'slug', 'ringkasan', 'konten',
        'gambar', 'penulis', 'tanggal_publish', 'unggulan', 'aktif',
    ];

    protected $casts = [
        'unggulan'        => 'boolean',
        'aktif'           => 'boolean',
        'tanggal_publish' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriArtikel::class, 'kategori_artikel_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
