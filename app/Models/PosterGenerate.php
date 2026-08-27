<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PosterGenerate extends Model
{
    protected $table = 'poster_generate';

    protected $fillable = [
        'rumah_sakit_id',
        'poster_template_id',
        'user_id',
        'jenis',
        'kategori_klinik',
        'tanggal',
        'nama_file',
        'path',
        'halaman',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'halaman' => 'integer',
    ];

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class);
    }

    public function posterTemplate(): BelongsTo
    {
        return $this->belongsTo(PosterTemplate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
