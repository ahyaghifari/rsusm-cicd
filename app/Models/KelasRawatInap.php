<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KelasRawatInap extends Model
{
    protected $table = 'kelas_rawat_inap';

    protected $fillable = ['rumah_sakit_id', 'nama', 'id_kelas_api', 'is_vip', 'public'];

    protected $casts = [
        'is_vip' => 'boolean',
        'public' => 'boolean',
    ];

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class);
    }

    public function rawatInap(): HasMany
    {
        return $this->hasMany(RawatInap::class, 'kelas_rawat_inap_id');
    }
}
