<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spesialis extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spesialis';

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
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the rumah sakit that owns the spesialis.
     */
    public function rumahSakit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }

    public function dokter(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Dokter::class, 'spesialis_id');
    }
}
