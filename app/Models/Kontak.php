<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kontak extends Model
{
    protected $table = 'kontak';

    protected $fillable = [
        'rumah_sakit_id',
        'label',
        'value',
        'gambar',
        'logo',
        'link',
        'kategori',
        'aktif',
    ];

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }
}
