<?php

namespace App\Filament\Pages;

use App\Models\JadwalHarianPerubahan;
use App\Models\PosterTemplate;
use App\Models\RumahSakit;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GeneratePerubahanJadwalPage extends Page
{
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon  = 'heroicon-o-bolt';
    protected static ?string $navigationLabel = 'Generate Perubahan Jadwal';
    protected static ?string $title           = 'Generate Poster Perubahan Jadwal';
    protected static ?string $navigationGroup = 'Poster Jadwal';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.resources.poster-jadwal-resource.pages.generate-perubahan-jadwal-page';

    // ── State ─────────────────────────────────────────────────────────────────

    public array $data = [];

    /** @var array<int, array{id:int, poliklinik_nama:string, dokter_nama:string, is_executive:bool, jam_awal:?string, jam_baru:?string, libur:bool, visible:bool}> */
    public array $perubahan_list = [];

    /** Pagination — 1 halaman = 1 file PNG terpisah. Section (Executive/Reguler) yang lebih panjang menentukan total halaman. */
    public int $activeHalaman = 1;
    public int $totalHalaman  = 1;

    public function mount(): void
    {
        $this->form->fill([
            'tanggal'        => now()->format('Y-m-d'),
            'rumah_sakit_id' => $this->currentUserRumahSakitId(),
        ]);
    }

    // ── Auth helpers ──────────────────────────────────────────────────────────

    private function currentUser(): User
    {
        /** @var User $user */
        $user = filament()->auth()->user();
        return $user;
    }

    private function isSuperAdmin(): bool
    {
        return $this->currentUser()->isSuperAdmin();
    }

    private function currentUserRumahSakitId(): ?int
    {
        return $this->currentUser()->rumah_sakit_id;
    }

    private function resolvedRumahSakitId(): ?int
    {
        return $this->isSuperAdmin()
            ? (int) ($this->data['rumah_sakit_id'] ?? 0) ?: null
            : $this->currentUserRumahSakitId();
    }

    /** Ambil PosterTemplate jenis Perubahan Jadwal, di-scope ke RS milik user (non-superadmin). */
    private function findTemplateForCurrentUser(?int $templateId, array $with = []): ?PosterTemplate
    {
        if (! $templateId) return null;

        $query = PosterTemplate::query()->with($with)->where('jenis', 'PERUBAHAN_JADWAL');

        if (! $this->isSuperAdmin()) {
            $query->where('rumah_sakit_id', $this->currentUserRumahSakitId());
        }

        return $query->find($templateId);
    }

    private function getTemplateId(): ?int
    {
        $val = $this->data['template_id'] ?? null;
        return $val ? (int) $val : null;
    }

    private function getTanggal(): ?string
    {
        return $this->data['tanggal'] ?? null;
    }

    // ── Form ──────────────────────────────────────────────────────────────────

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('rumah_sakit_id')
                            ->label('Rumah Sakit')
                            ->options(RumahSakit::pluck('nama', 'id'))
                            ->required()
                            ->visible(fn () => $this->isSuperAdmin())
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('template_id', null);
                                $this->perubahan_list = [];
                            }),

                        Forms\Components\Select::make('template_id')
                            ->label('Template Poster')
                            ->options(function () {
                                $rsId = $this->resolvedRumahSakitId();
                                if (! $rsId) return [];

                                return PosterTemplate::where('rumah_sakit_id', $rsId)
                                    ->where('jenis', 'PERUBAHAN_JADWAL')
                                    ->pluck('nama', 'id');
                            })
                            ->disabled(fn () => $this->isSuperAdmin() && ! $this->resolvedRumahSakitId())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get) => $this->loadPerubahanList($get)),

                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Pilih Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->default(now()->format('Y-m-d'))
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get) => $this->loadPerubahanList($get)),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    // ── Load Perubahan List ──────────────────────────────────────────────────

    public function loadPerubahanList(?Forms\Get $get = null): void
    {
        $templateId = $get ? ((int) $get('template_id') ?: null) : null;
        if (! $templateId) $templateId = $this->getTemplateId();

        $tanggal = $get ? $get('tanggal') : null;
        if (! $tanggal) $tanggal = $this->getTanggal();

        if (! $templateId || ! $tanggal) {
            $this->perubahan_list = [];
            return;
        }

        $template = $this->findTemplateForCurrentUser($templateId);
        if (! $template) { $this->perubahan_list = []; return; }

        $rsId          = $template->rumah_sakit_id;
        $parsedTanggal = Carbon::parse($tanggal)->format('Y-m-d');

        $this->perubahan_list = JadwalHarianPerubahan::query()
            ->whereHas('jadwalHarian', function ($q) use ($parsedTanggal, $rsId) {
                $q->whereDate('tanggal', $parsedTanggal)
                  ->whereHas('poliklinik', fn ($q2) => $q2->where('rumah_sakit_id', $rsId));
            })
            ->with(['jadwalHarian.poliklinik', 'jadwalHarian.dokter'])
            ->get()
            ->map(function (JadwalHarianPerubahan $p) {
                $jh = $p->jadwalHarian;

                $liburAwal = $p->status_layanan_asli?->value === 'LIBUR';
                $liburBaru = $p->status_layanan?->value === 'LIBUR';

                return [
                    'id'              => $p->id,
                    'poliklinik_nama' => $jh?->poliklinik?->nama ?? '-',
                    'dokter_nama'     => $jh?->nama_dokter ?: ($jh?->dokter?->nama ?? '-'),
                    'is_executive'    => (bool) ($jh?->is_executive ?? false),
                    'jam_awal'        => $liburAwal ? null : $this->formatRentang($p->jam_mulai_asli, $p->jam_selesai_asli),
                    'jam_baru'        => $liburBaru ? null : $this->formatRentang($p->jam_mulai, $p->jam_selesai),
                    'libur'           => $liburBaru,
                    'visible'         => true,
                ];
            })
            ->values()
            ->toArray();

        $this->recalcPagination($template);
    }

    private function formatRentang(?Carbon $mulai, ?Carbon $selesai): ?string
    {
        if (! $mulai) return null;
        return $mulai->format('H.i') . '--' . ($selesai?->format('H.i') ?? 'selesai');
    }

    public function togglePerubahan(int $index): void
    {
        if (isset($this->perubahan_list[$index])) {
            $this->perubahan_list[$index]['visible'] = ! $this->perubahan_list[$index]['visible'];
            $this->recalcPagination($this->findTemplateForCurrentUser($this->getTemplateId()));
        }
    }

    /**
     * Executive dan Reguler dipaginasi independen (berapa pun jumlah dokter yang berubah
     * di tiap section, batasnya sendiri-sendiri via config `max_dokter_per_halaman`).
     * Section yang lebih panjang menentukan total halaman poster.
     */
    private function recalcPagination(?PosterTemplate $template): void
    {
        $limit = (int) ($template?->config['grid']['max_dokter_per_halaman'] ?? 4) ?: 4;

        $visible          = collect($this->perubahan_list)->where('visible', true);
        $countExecutive   = $visible->where('is_executive', true)->count();
        $countReguler     = $visible->where('is_executive', false)->count();

        $halamanExecutive = (int) ceil($countExecutive / $limit);
        $halamanReguler   = (int) ceil($countReguler / $limit);

        $this->totalHalaman  = max(1, $halamanExecutive, $halamanReguler);
        $this->activeHalaman = min($this->activeHalaman, $this->totalHalaman);
    }

    public function setHalaman(int $halaman): void
    {
        $this->activeHalaman = max(1, min($halaman, $this->totalHalaman));
    }

    // ── Preview / Generate ───────────────────────────────────────────────────

    public function previewPerubahan(): void
    {
        $this->form->getState();

        [$template, $tanggal] = $this->resolveTemplateAndTanggal();
        if (! $template) return;

        if (! $this->hasVisiblePerubahan()) {
            Notification::make()->title('Tidak ada perubahan untuk ditampilkan.')->warning()->send();
            return;
        }

        $html = $this->buildHtml($template, $tanggal, $this->activeHalaman);

        $key  = Str::uuid()->toString();
        $path = storage_path("app/poster-preview/{$key}.html");
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, $html);

        $this->dispatch('open-preview', url: route('poster.preview', $key));
    }

    public function generate(): StreamedResponse|null
    {
        $this->form->getState();

        [$template, $tanggal] = $this->resolveTemplateAndTanggal();
        if (! $template) return null;

        if (! $this->hasVisiblePerubahan()) {
            Notification::make()->title('Tidak ada perubahan untuk ditampilkan.')->warning()->send();
            return null;
        }

        $html = $this->buildHtml($template, $tanggal, $this->activeHalaman);

        $outputPath = storage_path('app/public/poster-output/perubahan-' . $tanggal->format('Ymd') . '-' . time() . '.png');
        @mkdir(dirname($outputPath), 0755, true);

        try {
            $chromePath = config('services.browsershot.chrome_path');
            if (! $chromePath) {
                $chromePath = match (PHP_OS_FAMILY) {
                    'Windows' => 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
                    default   => collect([
                        '/usr/bin/google-chrome-stable',
                        '/usr/bin/google-chrome',
                        '/usr/bin/chromium-browser',
                        '/usr/bin/chromium',
                        '/snap/bin/chromium',
                        '/home/www/.cache/puppeteer/chrome/linux-149.0.7827.22/chrome-linux64/chrome',
                        '/root/.cache/puppeteer/chrome/linux-149.0.7827.22/chrome-linux64/chrome',
                    ])->first(fn ($p) => file_exists($p)),
                };
            }

            $browsershot = Browsershot::html($html)
                ->windowSize(1080, 1920)
                ->deviceScaleFactor(1)
                ->fullPage()
                ->timeout(60)
                ->addChromiumArguments([
                    'no-sandbox',
                    'disable-setuid-sandbox',
                    'disable-dev-shm-usage',
                    'disable-gpu',
                ])
                ->waitUntilNetworkIdle();

            if ($chromePath) {
                $browsershot->setChromePath($chromePath);
            }

            $browsershot->save($outputPath);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error render poster')
                ->body('Chrome path: ' . ($chromePath ?? 'null') . ' | ' . $e->getMessage())
                ->danger()
                ->send();
            return null;
        }

        $suffix = $this->totalHalaman > 1 ? "-hal{$this->activeHalaman}" : '';

        return response()->streamDownload(function () use ($outputPath) {
            readfile($outputPath);
            @unlink($outputPath);
        }, 'perubahan-jadwal-' . $tanggal->format('d-m-Y') . $suffix . '.png', [
            'Content-Type' => 'image/png',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function hasVisiblePerubahan(): bool
    {
        return collect($this->perubahan_list)->where('visible', true)->isNotEmpty();
    }

    private function resolveTemplateAndTanggal(): array
    {
        $templateId = $this->getTemplateId();
        $tanggalStr = $this->getTanggal();

        if (! $templateId || ! $tanggalStr) {
            Notification::make()->title('Pilih template dan tanggal terlebih dahulu.')->warning()->send();
            return [null, null];
        }

        $template = $this->findTemplateForCurrentUser($templateId, ['rumahSakit']);
        if (! $template) {
            Notification::make()->title('Template tidak ditemukan.')->danger()->send();
            return [null, null];
        }

        return [$template, Carbon::parse($tanggalStr)];
    }

    private function toDataUri(string $absolutePath): ?string
    {
        if (! file_exists($absolutePath)) return null;

        $mime = mime_content_type($absolutePath) ?: 'application/octet-stream';
        $b64  = base64_encode(file_get_contents($absolutePath));

        return "data:{$mime};base64,{$b64}";
    }

    /** Resolve upload fonts ke data URI (font_nama_klinik / font_isi). */
    private function resolveUploadFonts(PosterTemplate $template): array
    {
        $cfg    = $template->config ?? [];
        $result = [];

        $slots = [
            'FontNamaKlinik' => $cfg['grid']['font_nama_klinik'] ?? [],
            'FontIsi'        => $cfg['grid']['font_isi']         ?? [],
        ];

        foreach ($slots as $alias => $fontObj) {
            if (($fontObj['sumber'] ?? '') === 'upload' && ! empty($fontObj['path'])) {
                $abs = Storage::disk('public')->path($fontObj['path']);
                $uri = $this->toDataUri($abs);
                if ($uri) $result[$alias] = $uri;
            }
        }

        return $result;
    }

    /** Bangun data section (Executive/Reguler → poliklinik → items) untuk 1 halaman, lalu render HTML template. */
    private function buildHtml(PosterTemplate $template, Carbon $tanggal, int $halaman = 1): string
    {
        $g              = $template->config['grid'] ?? [];
        $labelExecutive = $g['label_executive'] ?? 'Klinik Executive';
        $labelReguler   = $g['label_reguler']   ?? 'Poliklinik Reguler';
        $limit          = (int) ($g['max_dokter_per_halaman'] ?? 4) ?: 4;

        $visible = collect($this->perubahan_list)->where('visible', true);

        // Slice per baris dokter (bukan per kartu poliklinik) supaya batas
        // "max_dokter_per_halaman" akurat, lalu regroup jadi kartu poliklinik.
        // ponytail: kalau 1 poliklinik punya dokter yang kepotong pas di batas
        // halaman, kartunya lanjut ke halaman berikutnya dengan judul diulang —
        // cukup untuk kasus wajar, upgrade ke "jangan potong tengah kartu" kalau
        // nanti dirasa perlu.
        $buildGroups = fn ($items) => $items
            ->values()
            ->slice(($halaman - 1) * $limit, $limit)
            ->groupBy('poliklinik_nama')
            ->map(fn ($rows, $poliklinikNama) => [
                'poliklinik_nama' => $poliklinikNama,
                'items' => $rows->map(fn ($r) => [
                    'dokter_nama' => $r['dokter_nama'],
                    'jam_awal'    => $r['jam_awal'],
                    'jam_baru'    => $r['jam_baru'],
                    'libur'       => $r['libur'],
                ])->values()->all(),
            ])
            ->values()
            ->all();

        $sections = [
            [
                'key'    => 'executive',
                'label'  => $labelExecutive,
                'groups' => $buildGroups($visible->where('is_executive', true)),
            ],
            [
                'key'    => 'reguler',
                'label'  => $labelReguler,
                'groups' => $buildGroups($visible->where('is_executive', false)),
            ],
        ];

        return view($template->layout()->templateView(), [
            'template'        => $template,
            'tanggal'         => $tanggal,
            'templateDataUri' => $this->toDataUri(Storage::disk('public')->path($template->template_png)),
            'uploadFonts'     => $this->resolveUploadFonts($template),
            'sections'        => $sections,
        ])->render();
    }
}
