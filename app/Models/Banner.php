<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array<string>
     */
    protected $fillable = [
        'rumah_sakit_id',
        'nama',
        'gambar',
        'sort_order',
        'aktif',
    ];

    /**
     * Cast tipe data kolom secara otomatis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'aktif'      => 'boolean',
        'sort_order' => 'integer',
    ];

    // =========================================================================
    //  RELATIONSHIPS
    // =========================================================================

    /**
     * Banner ini milik satu RumahSakit.
     *
     * Jika model target bernama RawatInap, ubah baris di bawah menjadi:
     *   return $this->belongsTo(RawatInap::class, 'rumah_sakit_id');
     */
    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }

    // =========================================================================
    //  SCOPES (Opsional — siap pakai jika dibutuhkan)
    // =========================================================================

    /**
     * Scope: hanya banner yang aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Scope: urutkan berdasarkan sort_order ascending.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}