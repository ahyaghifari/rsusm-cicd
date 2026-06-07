<?php

namespace App\Models;

use App\Enums\Hari;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class JadwalPraktek extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        $check = function (self $model) {
            $hari = $model->hari instanceof Hari ? $model->hari->value : $model->hari;

            $waktu = $model->waktu_mulai instanceof Carbon
                ? $model->waktu_mulai->format('H:i:s')
                : $model->waktu_mulai;

            $query = static::where('poliklinik_id', $model->poliklinik_id)
                ->where('hari', $hari)
                ->where('is_executive', (bool) $model->is_executive);

            if ($model->dokter_id) {
                $query->where('dokter_id', $model->dokter_id);
            } else {
                $query->whereNull('dokter_id')
                      ->where('nama_dokter', $model->nama_dokter);
            }

            $waktu
                ? $query->where('waktu_mulai', $waktu)
                : $query->whereNull('waktu_mulai');

            if ($model->exists) {
                $query->where('id', '!=', $model->id);
            }

            if ($query->exists()) {
                throw ValidationException::withMessages([
                    'dokter_id' => 'Jadwal dokter ini sudah ada untuk poliklinik, hari, jam mulai, dan tipe (regular/executive) yang sama.',
                ]);
            }
        };

        static::creating($check);
        static::updating($check);
    }

    protected $table = 'jadwal_praktek';

    protected $fillable = [
        'poliklinik_id',
        'hari',
        'dokter_id',
        'nama_dokter',
        'waktu_mulai',
        'waktu_selesai',
        'sesuai_perjanjian',
        'catatan',
        'is_executive',
    ];

    protected function casts(): array
    {
        return [
            'hari'              => Hari::class,
            'waktu_mulai'       => 'datetime:H:i',
            'waktu_selesai'     => 'datetime:H:i',
            'sesuai_perjanjian' => 'boolean',
            'is_executive'      => 'boolean',
        ];
    }

    public function poliklinik(): BelongsTo
    {
        return $this->belongsTo(PoliKlinik::class, 'poliklinik_id');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }
}
