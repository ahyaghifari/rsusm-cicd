<?php

namespace App\Models;

use App\Enums\Hari;
use App\Enums\JenisPosterTemplate;
use App\Filament\PosterLayouts\Contracts\PosterLayout;
use App\Filament\PosterLayouts\LayoutRegistry;
use App\Filament\PosterLayouts\PerubahanJadwalLayoutRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosterTemplate extends Model
{
    protected $fillable = [
        'rumah_sakit_id',
        'jenis',
        'nama',
        'template_png',
        'logo_header',
        'shape_poli',
        'config',
        'hari',
        'is_executive',
    ];

    protected $casts = [
        'jenis'        => JenisPosterTemplate::class,
        'config'       => 'array',
        'hari'         => Hari::class,
        'is_executive' => 'boolean',
    ];

    // ── Relasi ─────────────────────────────────────────────────────────────────

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class);
    }

    // ── Layout ─────────────────────────────────────────────────────────────────

    public function layout(): PosterLayout
    {
        return $this->jenis === JenisPosterTemplate::PERUBAHAN_JADWAL
            ? PerubahanJadwalLayoutRegistry::for((int) $this->rumah_sakit_id)
            : LayoutRegistry::for((int) $this->rumah_sakit_id);
    }

    public static function defaultConfig(?int $rumahSakitId = null, ?JenisPosterTemplate $jenis = null): array
    {
        if ($jenis === JenisPosterTemplate::PERUBAHAN_JADWAL) {
            return PerubahanJadwalLayoutRegistry::for($rumahSakitId ?? 1)->defaultConfig();
        }

        if ($rumahSakitId) {
            return LayoutRegistry::for($rumahSakitId)->defaultConfig();
        }

        // ponytail: fallback ke GridShape jika dipanggil tanpa konteks RS (legacy)
        return LayoutRegistry::for(1)->defaultConfig();
    }
}
