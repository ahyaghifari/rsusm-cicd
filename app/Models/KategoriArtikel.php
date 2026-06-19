<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriArtikel extends Model
{
    use HasFactory;

    protected $table = 'kategori_artikel';

    protected $fillable = ['rumah_sakit_id', 'nama', 'slug'];

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class);
    }

    public function artikel(): HasMany
    {
        return $this->hasMany(Artikel::class);
    }
}
