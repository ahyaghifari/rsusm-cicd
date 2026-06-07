<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    protected $table = 'faq';

    protected $fillable = [
        'rumah_sakit_id',
        'judul',
        'deskripsi',
        'kata_kunci',
        'sort_order',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
