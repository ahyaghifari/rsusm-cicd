<?php

namespace App\Filament\Pages;

use App\Models\JadwalHarian;
use App\Models\PoliKlinik;
use App\Models\PosterTemplate;
use App\Models\RumahSakit;
use App\Models\User;
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

class GeneratePosterPage extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Generate Poster';
    protected static ?string $title           = 'Generate Poster Jadwal';
    protected static ?string $navigationGroup = 'Poliklinik / Rawat Jalan';
    // protected static bool $shouldRegisterNavigation = false;
    protected static ?int $navigationSort = 4;
    protected static string  $view            = 'filament.resources.poster-jadwal-resource.pages.generate-poster-page';

    // ── State ─────────────────────────────────────────────────────────────────

    // Semua form field ditampung di $data — standar Filament Page.
    // FileUpload, Select, DatePicker semua bind ke sini via statePath('data').
    public array $data = [];

    /** @var array<int, array{id:int, nama:string, visible:bool, order:int}> */
    public array $poli_list = [];

    /** Whether the selected hospital supports executive clinics. */
    public bool $hospitalHasExecutiveClinic = false;

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

    /**
     * Resolve the active rumah_sakit_id:
     * - superadmin: uses the selected value from the form
     * - non-superadmin: uses their own rumah_sakit_id
     */
    private function resolvedRumahSakitId(): ?int
    {
        return $this->isSuperAdmin()
            ? (int) ($this->data['rumah_sakit_id'] ?? 0) ?: null
            : $this->currentUserRumahSakitId();
    }

    // ── Helpers akses $data ───────────────────────────────────────────────────

    private function getTemplateId(): ?int
    {
        $val = $this->data['template_id'] ?? null;
        return $val ? (int) $val : null;
    }

    private function getTanggal(): ?string
    {
        return $this->data['tanggal'] ?? null;
    }

    private function getFotoHero(): ?string
    {
        $val = $this->data['foto_hero'] ?? null;
        if (is_array($val)) $val = array_values($val)[0] ?? null;
        return is_string($val) && $val !== '' ? $val : null;
    }

    private function getFotoHeroDataUri(): ?string
    {
        $val = $this->getFotoHero();
        if (! $val) return null;
        $path = Storage::disk('public')->path($val);
        return file_exists($path) ? $this->toDataUri($path) : null;
    }

    private function getKeterangan(): string
    {
        return $this->data['keterangan'] ?? '';
    }

    private function getExecutiveClinicFilter(): string
    {
        return $this->data['executive_clinic_filter'] ?? 'reguler_dan_eksekutif';
    }

    // ── Form ──────────────────────────────────────────────────────────────────

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        // ── Rumah Sakit — only visible for superadmins ─────────
                        Forms\Components\Select::make('rumah_sakit_id')
                            ->label('Rumah Sakit')
                            ->options(RumahSakit::pluck('nama', 'id'))
                            ->required()
                            ->visible(fn () => $this->isSuperAdmin())
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('template_id', null);
                                $set('executive_clinic_filter', 'reguler_dan_eksekutif');
                                $this->poli_list = [];
                                $this->hospitalHasExecutiveClinic = false;
                            }),

                        Forms\Components\Select::make('template_id')
                            ->label('Template Poster')
                            ->options(function (Forms\Get $get) {
                                $rsId = $this->resolvedRumahSakitId();
                                if (! $rsId) return [];

                                return PosterTemplate::where('rumah_sakit_id', $rsId)
                                    ->pluck('nama', 'id');
                            })
                            ->disabled(fn () => $this->isSuperAdmin() && ! $this->resolvedRumahSakitId())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get) {
                                $templateId = (int) $get('template_id') ?: null;
                                if ($templateId) {
                                    $rsId = PosterTemplate::where('id', $templateId)->value('rumah_sakit_id');
                                    $this->hospitalHasExecutiveClinic = (bool) RumahSakit::where('id', $rsId)->value('executive_clinic');
                                } else {
                                    $this->hospitalHasExecutiveClinic = false;
                                }
                                $this->loadPoliList($get);
                            }),

                        Forms\Components\Select::make('executive_clinic_filter')
                            ->label('Filter Klinik')
                            ->options([
                                'reguler'               => 'Reguler',
                                'eksekutif'             => 'Eksekutif',
                                'reguler_dan_eksekutif' => 'Reguler dan Eksekutif',
                            ])
                            ->default('reguler_dan_eksekutif')
                            ->required()
                            ->visible(fn () => $this->hospitalHasExecutiveClinic)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get) => $this->loadPoliList($get)),

                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Pilih Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->default(now()->format('Y-m-d'))
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get) => $this->loadPoliList($get)),

                        Forms\Components\FileUpload::make('foto_hero')
                            ->label('Upload Foto Hero')
                            ->image()
                            ->directory('poster-tmp')
                            ->disk('public')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png'])
                            ->helperText('Foto yang akan menjadi background layer bawah poster.'),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan Hero')
                            ->placeholder('Contoh: Tindakan EXILIS Aurora EC')
                            ->rows(2),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');  // ← kunci: binding ke $this->data
    }

    // ── Load Poli List ────────────────────────────────────────────────────────

    public function loadPoliList(?Forms\Get $get = null): void
    {
        $templateId = $get ? ((int) $get('template_id') ?: null) : null;
        if (! $templateId) $templateId = $this->getTemplateId();

        $tanggal = $get ? $get('tanggal') : null;
        if (! $tanggal) $tanggal = $this->getTanggal();

        if (! $templateId || ! $tanggal) {
            $this->poli_list = [];
            return;
        }

        $template = PosterTemplate::find($templateId);
        if (! $template) { $this->poli_list = []; return; }

        $rsId = $template->rumah_sakit_id;

        $rs = RumahSakit::find($rsId);
        $this->hospitalHasExecutiveClinic = (bool) ($rs?->executive_clinic);

        // Read executive clinic filter value
        $filter = $get ? $get('executive_clinic_filter') : null;
        if (! $filter) $filter = $this->getExecutiveClinicFilter();
        if (! $filter) $filter = 'reguler_dan_eksekutif';

        // Ensure tanggal only contains the date part (Y-m-d) in case frontend DatePicker sends a full datetime string
        $parsedTanggal = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');

        $this->poli_list = PoliKlinik::where('rumah_sakit_id', $rsId)
            ->where('aktif', true)
            ->whereHas('jadwalHarian', function ($q) use ($parsedTanggal, $filter) {
                $q->whereDate('tanggal', $parsedTanggal)
                  ->when($filter === 'reguler',   fn ($q) => $q->where('is_executive', 0))
                  ->when($filter === 'eksekutif', fn ($q) => $q->where('is_executive', 1));
            })
            ->orderBy('nama')
            ->get()
            ->map(fn ($poli) => [
                'id'      => $poli->id,
                'nama'    => $poli->nama,
                'visible' => true,
                'order'   => $poli->urutan ?? 999,
            ])
            ->values()
            ->toArray();

        \Illuminate\Support\Facades\Log::info('loadPoliList execution', [
            'templateId' => $templateId,
            'tanggal'    => $tanggal,
            'filter'     => $filter,
            'count'      => count($this->poli_list),
        ]);
    }

    // ── Poli List Actions ─────────────────────────────────────────────────────

    public function togglePoli(int $index): void
    {
        if (isset($this->poli_list[$index])) {
            $this->poli_list[$index]['visible'] = ! $this->poli_list[$index]['visible'];
        }
    }

    public function reorderPoli(int $oldIndex, int $newIndex): void
    {
        if ($oldIndex === $newIndex) return;

        $list = $this->poli_list;
        $item = array_splice($list, $oldIndex, 1)[0];
        array_splice($list, $newIndex, 0, [$item]);

        foreach ($list as $i => &$row) {
            $row['order'] = $i + 1;
        }
        unset($row);

        $this->poli_list = $list;
    }

    public function previewPoster(): void
    {
        Storage::disk('public')->makeDirectory('poster-tmp');
        $this->form->getState();

        [$template, $tanggal] = $this->resolveTemplateAndTanggal();
        if (! $template) return;

        if (! $this->hasJadwalHarianData($template->rumah_sakit_id, $tanggal)) {
            Notification::make()
                ->title('Belum ada Jadwal Harian')
                ->body('Silakan isi jadwal harian terlebih dahulu untuk tanggal yang dipilih.')
                ->warning()
                ->send();
            return;
        }

        if (empty($this->poli_list)) {
            Notification::make()
                ->title('Tidak ada poliklinik untuk ditampilkan.')
                ->body('Tidak ada jadwal harian pada tanggal ini.')
                ->warning()
                ->send();
            return;
        }

        $html = $this->buildHtml($template, $tanggal);

        $key  = Str::uuid()->toString();
        $path = storage_path("app/poster-preview/{$key}.html");
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, $html);

        $this->dispatch('open-preview', url: route('poster.preview', $key));
    }

    // ── Generate ──────────────────────────────────────────────────────────────

    public function generate(): StreamedResponse|null
    {
        Storage::disk('public')->makeDirectory('poster-tmp');
        $this->form->getState();

        [$template, $tanggal] = $this->resolveTemplateAndTanggal();
        if (! $template) return null;

        if (! $this->hasJadwalHarianData($template->rumah_sakit_id, $tanggal)) {
            Notification::make()
                ->title('Belum ada Jadwal Harian')
                ->body('Silakan isi jadwal harian terlebih dahulu untuk tanggal yang dipilih.')
                ->warning()
                ->send();
            return null;
        }

        $visibleCount = collect($this->poli_list)->where('visible', true)->count();
        if ($visibleCount === 0) {
            Notification::make()
                ->title('Minimal 1 poliklinik harus ditampilkan.')
                ->body('Pastikan ada poliklinik yang dicentang pada daftar poli.')
                ->warning()
                ->send();
            return null;
        }

        $html     = $this->buildHtml($template, $tanggal);
        $fotoHero = $this->getFotoHero();

        $outputPath = storage_path('app/public/poster-output/poster-' . $tanggal->format('Ymd') . '-' . time() . '.png');
        @mkdir(dirname($outputPath), 0755, true);

        try {
            $chromePath = env('CHROME_PATH');
            if (! $chromePath) {
                $chromePath = match (PHP_OS_FAMILY) {
                    'Windows' => 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
                    default => '/usr/bin/chromium-browser'
                };
            }

            Browsershot::html($html)
                ->setChromePath($chromePath)
                ->windowSize(1080, 1920)
                ->deviceScaleFactor(1)
                ->fullPage()
                ->waitUntilNetworkIdle()
                ->save($outputPath);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error render poster')
                ->body($e->getMessage())
                ->danger()
                ->send();
            return null;
        }

        if ($fotoHero) {
            Storage::disk('public')->delete($fotoHero);
        }

        return response()->streamDownload(function () use ($outputPath) {
            readfile($outputPath);
            @unlink($outputPath);
        }, 'poster-jadwal-' . $tanggal->format('d-m-Y') . '.png', [
            'Content-Type' => 'image/png',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
 
    /**
     * Konversi file lokal ke data URI base64.
     * Browsershot tidak mengizinkan file:// — data URI adalah solusinya.
     */
    private function toDataUri(string $absolutePath): ?string
    {
        if (! file_exists($absolutePath)) {
            return null;
        }
 
        $mime = mime_content_type($absolutePath) ?: 'application/octet-stream';
        $b64  = base64_encode(file_get_contents($absolutePath));
 
        return "data:{$mime};base64,{$b64}";
    }
 
    /**
     * Resolve upload fonts ke data URI agar bisa di-embed via @font-face di HTML.
     * Return: ['alias' => 'data:font/...;base64,...']
     */
    private function resolveUploadFonts(PosterTemplate $template): array
    {
        $cfg    = $template->config ?? [];
        $result = [];
 
        $slots = [
            'FontTanggal'    => $cfg['font_tanggal']          ?? [],
            'FontKeterangan' => $cfg['font_keterangan']       ?? [],
            'FontIsi'        => $cfg['grid']['font_isi']      ?? [],
            'FontNamaPoli'   => $cfg['grid']['font_nama_poli'] ?? [],
        ];
 
        foreach ($slots as $alias => $fontObj) {
            if (($fontObj['sumber'] ?? '') === 'upload' && ! empty($fontObj['path'])) {
                $abs = Storage::disk('public')->path($fontObj['path']);
                $uri = $this->toDataUri($abs);
                if ($uri) {
                    $result[$alias] = $uri;
                }
            }
        }
 
        return $result;
    }

    /** Check whether jadwal harian exists for a given RS and date. */
    private function hasJadwalHarianData(int $rsId, Carbon $tanggal): bool
    {
        return JadwalHarian::whereDate('tanggal', $tanggal)
            ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rsId))
            ->exists();
    }

    /** Validasi & parse template + tanggal. Return [null, null] jika gagal. */
    private function resolveTemplateAndTanggal(): array
    {
        $templateId = $this->getTemplateId();
        $tanggalStr = $this->getTanggal();

        if (! $templateId || ! $tanggalStr) {
            Notification::make()->title('Pilih template dan tanggal terlebih dahulu.')->warning()->send();
            return [null, null];
        }

        $template = PosterTemplate::with('rumahSakit')->find($templateId);
        if (! $template) {
            Notification::make()->title('Template tidak ditemukan.')->danger()->send();
            return [null, null];
        }

        return [$template, Carbon::parse($tanggalStr)];
    }

    /** Bangun data jadwal per poli dari jadwal harian, lalu render HTML template. */
    private function buildHtml(PosterTemplate $template, Carbon $tanggal): string
    {
        $rsId = $template->rumah_sakit_id;

        $filter = $this->getExecutiveClinicFilter();

        $jadwalHarian = JadwalHarian::whereDate('tanggal', $tanggal)
            ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rsId))
            ->when($filter === 'reguler',   fn ($q) => $q->where('is_executive', 0))
            ->when($filter === 'eksekutif', fn ($q) => $q->where('is_executive', 1))
            ->with(['poliklinik', 'dokter'])
            ->get()
            ->groupBy('poliklinik_id');

        $poliList = collect($this->poli_list)
            ->where('visible', true)
            ->sortBy('order')
            ->values()
            ->map(function ($item) use ($jadwalHarian, $rsId) {
                $rows = $jadwalHarian->get($item['id'], collect());
                $poli = $rows->first()?->poliklinik
                    ?? PoliKlinik::where('id', $item['id'])->where('rumah_sakit_id', $rsId)->first();
                if (! $poli) return null;

                return [
                    'poli'   => $poli,
                    'jadwal' => $rows->map(fn ($r) => [
                        'nama_dokter'       => $r->dokter?->nama ?? $r->nama_dokter ?? '-',
                        'jam_mulai'         => $r->jam_mulai?->format('H:i'),
                        'jam_selesai'       => $r->jam_selesai?->format('H:i'),
                        'libur'             => ($r->status_layanan?->value ?? '') === 'LIBUR',
                        'is_executive'      => (bool) ($r->is_executive ?? false),
                        'sesuai_perjanjian' => (bool) ($r->sesuai_perjanjian ?? false),
                    ])->toArray(),
                ];
            })
            ->filter(fn ($p) => ! empty($p['jadwal']))
            ->values();

        return view('filament.resources.poster-jadwal-resource.pages.jadwal-template', [
            'template'        => $template,
            'tanggal'         => $tanggal,
            'fotoHeroDataUri' => $this->getFotoHeroDataUri(),
            'templateDataUri' => $this->toDataUri(Storage::disk('public')->path($template->template_png)),
            'logoDataUri'     => $template->logo_header
                                    ? $this->toDataUri(Storage::disk('public')->path($template->logo_header))
                                    : null,
            'uploadFonts'     => $this->resolveUploadFonts($template),
            'keterangan'      => $this->getKeterangan(),
            'poliList'        => $poliList,
        ])->render();
    }
}