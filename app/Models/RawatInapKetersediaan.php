<?php

namespace App\Models;

use App\Enums\StatusKetersediaanKamar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawatInapKetersediaan extends Model
{
    protected $table = 'rawat_inap_ketersediaan';

    protected $fillable = [
        'rumah_sakit_id', 'external_id', 'ruang_kamar', 'tempat_tidur', 'status',
        'tanggal_update_api', 'keterangan', 'ruangan', 'nama_kamar', 'kelas_rawat_inap_id',
        'synced_at',
    ];

    protected $casts = [
        'status'             => 'integer',
        'tanggal_update_api' => 'datetime',
        'synced_at'          => 'datetime',
    ];

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class);
    }

    public function kelasRawatInap(): BelongsTo
    {
        return $this->belongsTo(KelasRawatInap::class, 'kelas_rawat_inap_id');
    }

    public function getStatusEnumAttribute(): ?StatusKetersediaanKamar
    {
        return StatusKetersediaanKamar::tryFrom($this->status);
    }
}
