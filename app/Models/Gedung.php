<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gedung extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gedung';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the rumah sakit that owns the gedung.
     */
    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }

    /**
     * Get the rawat inap records in this gedung.
     */
    public function rawatInap(): HasMany
    {
        return $this->hasMany(RawatInap::class, 'gedung_id');
    }

   
}
