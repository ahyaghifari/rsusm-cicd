<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Promo extends Model
{
    use HasFactory;

    /**
     * Nama tabel eksplisit karena bukan bentuk plural standar Laravel.
     */
    protected $table = 'promo';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array<string>
     */
    protected $fillable = [
        'rumah_sakit_id',
        'judul',
        'slug',
        'deskripsi',
        'gambar',
        'popup',
        'aktif',
    ];

    /**
     * Cast tipe data kolom secara otomatis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'popup' => 'boolean',
        'aktif' => 'boolean',
    ];

    // =========================================================================
    //  BOOT
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->judul, $model->rumah_sakit_id);
            }
        });
    }

    protected static function generateUniqueSlug(string $title, ?int $rumahSakitId = null): string
    {
        $base = Str::slug($title) ?: 'promo';
        $slug = $base;
        $i    = 1;
        while (static::where('slug', $slug)
            ->when($rumahSakitId, fn ($q) => $q->where('rumah_sakit_id', $rumahSakitId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    // =========================================================================
    //  RELATIONSHIPS
    // =========================================================================

    /**
     * Promo ini milik satu RumahSakit.
     */
    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class);
    }

    // =========================================================================
    //  SCOPES
    // =========================================================================

    /**
     * Scope: hanya promo yang aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Scope: hanya promo popup.
     */
    public function scopePopup($query)
    {
        return $query->where('popup', true);
    }
}