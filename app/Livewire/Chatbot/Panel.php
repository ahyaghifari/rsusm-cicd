<?php

namespace App\Livewire\Chatbot;

use Livewire\Component;
use App\Models\RumahSakit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Panel extends Component
{
    private const SESSION_KEY  = 'chatbot_state';
    private const MAX_MESSAGES = 80;

    public bool $branchSelected = false;
    public ?string $activeBranchSlug = null;
    public ?RumahSakit $activeBranch = null;
    public array $messages = [];
    public string $inputMessage = '';
    public string $sessionKey = '';

    public function mount(): void
    {
        $saved = session(self::SESSION_KEY, []);
        if (empty($saved)) return;

        $this->sessionKey      = $saved['sessionKey'] ?? '';
        $this->activeBranchSlug = $saved['activeBranchSlug'] ?? null;
        $this->messages        = array_slice($saved['messages'] ?? [], -self::MAX_MESSAGES);
        $this->branchSelected  = $saved['branchSelected'] ?? false;

        if ($this->activeBranchSlug) {
            $this->activeBranch = RumahSakit::where('slug', $this->activeBranchSlug)
                ->where('aktif', true)
                ->first();

            if (! $this->activeBranch) {
                $this->branchSelected   = false;
                $this->activeBranchSlug = null;
                $this->messages         = [];
            }
        }
    }

    private function saveState(): void
    {
        session([self::SESSION_KEY => [
            'sessionKey'       => $this->sessionKey,
            'activeBranchSlug' => $this->activeBranchSlug,
            'branchSelected'   => $this->branchSelected,
            'messages'         => array_slice($this->messages, -self::MAX_MESSAGES),
        ]]);
    }

    protected array $responses = [
        'jadwal dokter' => [
            'card' => [
                ['icon' => 'ti-calendar', 'text' => 'Senin – Sabtu, 07.00 – 21.00 WITA'],
                ['icon' => 'ti-stethoscope', 'text' => 'Poli Umum & berbagai Spesialis'],
            ],
            'opts' => ['Daftar spesialis', 'Buat janji temu'],
        ],
        'pendaftaran' => [
            'card' => [
                ['icon' => 'ti-device-mobile', 'text' => 'Hubungi hotline atau datang langsung'],
                ['icon' => 'ti-clock', 'text' => 'Daftar minimal 1 hari sebelumnya'],
                ['icon' => 'ti-id-badge', 'text' => 'Siapkan KTP & kartu BPJS (jika ada)'],
            ],
            'opts' => ['Hubungi admin', 'Info BPJS'],
        ],
        'info igd' => [
            'card' => [
                ['icon' => 'ti-ambulance', 'text' => 'Buka 24 jam, 7 hari seminggu'],
                ['icon' => 'ti-alert-triangle', 'text' => 'Segera hubungi untuk kondisi darurat'],
            ],
            'opts' => ['Lokasi rumah sakit', 'Fasilitas IGD'],
        ],
        'fasilitas' => [
            'card' => [
                ['icon' => 'ti-microscope', 'text' => 'Laboratorium & Radiologi 24 jam'],
                ['icon' => 'ti-bed', 'text' => 'Rawat inap kelas I, II, III & VIP'],
                ['icon' => 'ti-heart-rate-monitor', 'text' => 'ICU, kamar operasi & poli spesialis'],
            ],
            'opts' => ['Info rawat inap', 'Poli spesialis'],
        ],
        'biaya' => [
            'card' => [
                ['icon' => 'ti-credit-card', 'text' => 'Menerima BPJS Kesehatan'],
                ['icon' => 'ti-building-bank', 'text' => 'Kerjasama asuransi swasta'],
                ['icon' => 'ti-receipt', 'text' => 'Informasi tarif detail via admin/hotline'],
            ],
            'opts' => ['Info BPJS', 'Hubungi admin keuangan'],
        ],
        'lokasi' => [
            'card' => [
                ['icon' => 'ti-clock', 'text' => 'IGD & rawat inap buka 24 jam'],
                ['icon' => 'ti-parking', 'text' => 'Tersedia parkir kendaraan'],
            ],
            'opts' => ['Petunjuk arah', 'Kontak rumah sakit'],
        ],
    ];

    public function selectBranch(string $slug): void
    {
        $branch = RumahSakit::where('slug', $slug)->where('aktif', true)->first();

        if (! $branch) return;

        $this->activeBranch = $branch;
        $this->activeBranchSlug = $slug;
        $this->branchSelected = true;
        $this->messages = [];

        $this->addBotMessage(
            "Halo! Selamat datang di <strong>{$branch->nama}</strong>.<br>Ada yang bisa saya bantu hari ini?",
            [
                ['icon' => 'ti-map-pin', 'text' => $branch->lokasi],
                ['icon' => 'ti-phone', 'text' => 'Hotline: ' . ($branch->no_hotline !== '-' ? $branch->no_hotline : 'Belum tersedia')],
                ['icon' => 'ti-ambulance', 'text' => 'IGD: ' . ($branch->no_emergency !== '-' ? $branch->no_emergency : 'Datang langsung')],
                ['icon' => 'ti-clock-24', 'text' => 'Layanan 24 jam'],
            ],
            ['Jadwal dokter', 'Pendaftaran', 'Info IGD']
        );

        $this->saveState();
    }

    public function changeBranch(): void
    {
        $this->branchSelected   = false;
        $this->activeBranch     = null;
        $this->activeBranchSlug = null;
        $this->messages         = [];
        $this->inputMessage     = '';
        $this->sessionKey       = '';
        $this->saveState();
    }

    private function sendToAi($text){
        // Generate sekali saat pesan pertama, simpan untuk sesi berikutnya
        if (empty($this->sessionKey)) {
            $this->sessionKey = Str::uuid()->toString();
        }

        $response = Http::timeout(60)->post(env("N8N_URL", "http://localhost:5678"), [
            'chatInput'  => $text,
            'branch'     => $this->activeBranch->slug,
            'sessionKey' => $this->sessionKey,
        ]);

        if ($response->successful()) {
            // Status code 200 - 299
            $json = $response->json();
            return $json['output'];
        }

        if ($response->failed()) {
            return 'ERROR';
            // Status code 400 atau 500 ke atas
            // Log::error('API bermasalah');
        }
    }

    public function sendMessage(string $text = ''): void
    {
        // Teks bisa datang langsung sebagai parameter (dari Alpine) atau dari $inputMessage
        if ($text === '') {
            $text = $this->inputMessage;
        }
        $text = trim(mb_substr($text, 0, 150));
        if (! $text || ! $this->branchSelected) return;

        $this->inputMessage = '';

        $this->addUserMessage($text);

        $respon = $this->sendToAi($text);
        $this->addBotMessage($respon);
        $this->saveState();
    }

    public function sendQuick(string $text): void
    {
        if (! $this->branchSelected) return;
        $this->addUserMessage($text);
        $this->generateReply($text);
        $this->saveState();
    }

    protected function addUserMessage(string $text): void
    {
        $this->messages[] = [
            'type' => 'user',
            'text' => e($text),
            'time' => now()->format('H:i'),
        ];
    }

    protected function addBotMessage(string $text, array $card = [], array $opts = []): void
    {
        $this->messages[] = [
            'type' => 'bot',
            'text' => $text,
            'time' => now()->format('H:i'),
        ];
    }

    public function render()
    {
        $branches = RumahSakit::where('aktif', true)->get();
        return view('rumah_sakit.chatbot.panel', compact('branches'));
    }
}