<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RumahSakit extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rumah_sakit';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'slug',
        'lokasi',
        'alamat',
        'no_emergency',
        'no_hotline',
        'gambar',
        'logo',
        'aktif',
        'link_pendaftaran_online',
        'lokasi_google_map',
        'tentang_kami',
        'gambar_tentang',
    ];

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
     * Get the gedungs for the rumah sakit.
     */
    public function gedung(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Gedung::class, 'rumah_sakit_id');
    }

    /**
     * Get the rawat inap records for the rumah sakit.
     */
    public function rawatInap(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RawatInap::class, 'rumah_sakit_id');
    }

    public function linkLayanan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LinkLayanan::class, 'rumah_sakit_id');
    }
}
