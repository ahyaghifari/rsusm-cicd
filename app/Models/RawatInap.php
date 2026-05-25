<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawatInap extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rawat_inap';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'aktif' => 'boolean',
    ];

    /**
     * Get the rumah sakit that owns the rawat inap.
     */
    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }

    /**
     * Get the gedung that owns the rawat inap.
     */
    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class, 'gedung_id');
    }

    /**
     * Get the images for the rawat inap.
     */
    public function gambar(): HasMany
    {
        return $this->hasMany(GambarRawatInap::class, 'rawat_inap_id');
    }

    /**
     * Get the facilities for the rawat inap.
     */
    public function fasilitasRawatInap(): HasMany
    {
        return $this->hasMany(FasilitasRawatInap::class, 'rawat_inap_id');
    }
}
