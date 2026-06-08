<?php

namespace App\Models;

use App\Enums\PengirimPesan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KonsultasiPesan extends Model
{
    use HasFactory;

    protected $table = 'konsultasi_pesan';

    protected $fillable = [
        'sesi_id',
        'pengirim',
        'isi',
        'dibaca_at',
    ];

    protected $casts = [
        'pengirim'  => PengirimPesan::class,
        'dibaca_at' => 'datetime',
    ];

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiKonsultasi::class, 'sesi_id');
    }
}
