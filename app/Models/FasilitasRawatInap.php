<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FasilitasRawatInap extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fasilitas_rawat_inap';

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
     * Get the rawat inap that owns the facility.
     */
    public function rawatInap(): BelongsTo
    {
        return $this->belongsTo(RawatInap::class, 'rawat_inap_id');
    }
}
