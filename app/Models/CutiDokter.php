<?php

namespace App\Models;

use App\Enums\StatusLayanan;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CutiDokter extends Model
{
    protected $table = 'cuti_dokter';

    protected $fillable = [
        'rumah_sakit_id',
        'dokter_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai'   => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    private const HARI = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];

    protected static function booted(): void
    {
        static::saving(function (self $cuti) {
            $cuti->rumah_sakit_id = Dokter::whereKey($cuti->dokter_id)->value('rumah_sakit_id');
            $cuti->user_id ??= Auth::id();
        });

        static::created(fn (self $cuti) => DB::transaction(fn () => static::terapkan($cuti->snapshot())));

        static::updated(fn (self $cuti) => DB::transaction(function () use ($cuti) {
            static::pulihkan($cuti->getOriginal());
            static::terapkan($cuti->snapshot());
        }));

        static::deleted(fn (self $cuti) => DB::transaction(fn () => static::pulihkan($cuti->snapshot())));
    }

    /** Data cuti dengan tanggal Y-m-d (tanpa konversi zona waktu serialisasi). */
    private function snapshot(): array
    {
        return [
            'dokter_id'       => $this->dokter_id,
            'tanggal_mulai'   => $this->tanggal_mulai->toDateString(),
            'tanggal_selesai' => $this->tanggal_selesai->toDateString(),
            'alasan'          => $this->alasan,
            'user_id'         => $this->user_id,
        ];
    }

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class, 'rumah_sakit_id');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Cuti yang mencakup $tanggal (Y-m-d). */
    public function scopeAktifPada(Builder $query, string $tanggal): Builder
    {
        return $query->whereDate('tanggal_mulai', '<=', $tanggal)
                     ->whereDate('tanggal_selesai', '>=', $tanggal);
    }

    /**
     * Isi JadwalHarian untuk setiap hari praktek dokter dalam rentang cuti:
     * status LIBUR + catatan = alasan. Baris yang belum ada dibuat dari JadwalPraktek.
     */
    private static function terapkan(array $cuti): void
    {
        $period = CarbonPeriod::create(Carbon::parse($cuti['tanggal_mulai']), Carbon::parse($cuti['tanggal_selesai']));

        foreach ($period as $tgl) {
            $tanggal = $tgl->toDateString();

            $grup = JadwalPraktek::where('dokter_id', $cuti['dokter_id'])
                ->where('hari', self::HARI[$tgl->dayOfWeek])
                ->whereHas('poliklinik', fn ($q) => $q->where('aktif', true)->where('jadwal_tidak_tetap', false))
                ->get()
                ->groupBy(fn ($jp) => $jp->poliklinik_id . '|' . (int) $jp->is_executive);

            foreach ($grup as $jps) {
                $first = $jps->first();

                $rows = JadwalHarian::whereDate('tanggal', $tanggal)
                    ->where('poliklinik_id', $first->poliklinik_id)
                    ->where('dokter_id', $cuti['dokter_id'])
                    ->where('is_executive', $first->is_executive)
                    ->get();

                if ($rows->isEmpty()) {
                    $rows = $jps->map(fn ($jp) => JadwalHarian::create([
                        'poliklinik_id'     => $jp->poliklinik_id,
                        'tanggal'           => $tanggal,
                        'dokter_id'         => $jp->dokter_id,
                        'nama_dokter'       => $jp->nama_dokter,
                        'jam_mulai'         => $jp->waktu_mulai?->format('H:i'),
                        'jam_selesai'       => $jp->waktu_selesai?->format('H:i'),
                        'status_layanan'    => 'BUKA',
                        'catatan'           => $jp->catatan,
                        'is_executive'      => $jp->is_executive,
                        'sesuai_perjanjian' => $jp->sesuai_perjanjian,
                        'sumber'            => 'GENERATE',
                    ]));
                }

                $rows->each(fn ($jh) => static::tandaiLibur($jh, $cuti['alasan'], $cuti['user_id'] ?? null));
            }
        }
    }

    /** Kembalikan baris LIBUR hasil cuti (cocok dokter, rentang, & catatan = alasan) ke BUKA. */
    private static function pulihkan(array $cuti): void
    {
        $rows = JadwalHarian::with('perubahan')
            ->where('dokter_id', $cuti['dokter_id'])
            ->whereDate('tanggal', '>=', Carbon::parse($cuti['tanggal_mulai'])->toDateString())
            ->whereDate('tanggal', '<=', Carbon::parse($cuti['tanggal_selesai'])->toDateString())
            ->where('status_layanan', StatusLayanan::LIBUR->value)
            ->where('catatan', $cuti['alasan'])
            ->get();

        foreach ($rows as $jh) {
            $master = JadwalPraktek::where('dokter_id', $jh->dokter_id)
                ->where('poliklinik_id', $jh->poliklinik_id)
                ->where('is_executive', $jh->is_executive)
                ->where('hari', self::HARI[$jh->tanggal->dayOfWeek])
                ->first();

            $jh->perubahan?->delete();
            $jh->update(['status_layanan' => 'BUKA', 'catatan' => $master?->catatan]);
        }
    }

    /**
     * Set baris jadwal harian jadi LIBUR dan catat di JadwalHarianPerubahan
     * (nilai asli dipertahankan kalau sudah pernah berubah). Dipakai juga oleh cron generate.
     */
    public static function tandaiLibur(JadwalHarian $jh, string $alasan, ?int $userId): void
    {
        $perubahan = $jh->perubahan;

        // Sudah LIBUR manual (bukan hasil perubahan) — jangan ditimpa.
        if ($jh->status_layanan === StatusLayanan::LIBUR && ! $perubahan) {
            return;
        }

        $asli = $perubahan
            ? [$perubahan->jam_mulai_asli, $perubahan->jam_selesai_asli, $perubahan->status_layanan_asli?->value]
            : [$jh->jam_mulai, $jh->jam_selesai, $jh->status_layanan->value];

        $jh->update(['status_layanan' => 'LIBUR', 'catatan' => $alasan]);

        JadwalHarianPerubahan::updateOrCreate(
            ['jadwal_harian_id' => $jh->id],
            [
                'user_id'             => $userId,
                'jam_mulai'           => $jh->jam_mulai?->format('H:i'),
                'jam_selesai'         => $jh->jam_selesai?->format('H:i'),
                'status_layanan'      => 'LIBUR',
                'jam_mulai_asli'      => $asli[0]?->format('H:i'),
                'jam_selesai_asli'    => $asli[1]?->format('H:i'),
                'status_layanan_asli' => $asli[2],
                'catatan'             => $alasan,
            ]
        );
    }
}
