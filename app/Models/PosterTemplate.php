<?php

namespace App\Models;

use App\Filament\PosterLayouts\Contracts\PosterLayout;
use App\Filament\PosterLayouts\LayoutRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosterTemplate extends Model
{
    protected $fillable = [
        'rumah_sakit_id',
        'nama',
        'template_png',
        'logo_header',
        'shape_poli',
        'config',
        'is_default',
    ];

    protected $casts = [
        'config'     => 'array',
        'is_default' => 'boolean',
    ];

    // ── Relasi ─────────────────────────────────────────────────────────────────

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class);
    }

    // ── Layout ─────────────────────────────────────────────────────────────────

    public function layout(): PosterLayout
    {
        return LayoutRegistry::for((int) $this->rumah_sakit_id);
    }

    public static function defaultConfig(?int $rumahSakitId = null): array
    {
        if ($rumahSakitId) {
            return LayoutRegistry::for($rumahSakitId)->defaultConfig();
        }

        // ponytail: fallback ke GridShape jika dipanggil tanpa konteks RS (legacy)
        return LayoutRegistry::for(1)->defaultConfig();
    }
}
