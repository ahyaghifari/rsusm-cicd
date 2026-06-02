<?php

namespace App\Livewire\Chatbot;

use Livewire\Component;
use App\Models\RumahSakit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Panel extends Component
{
    public bool $branchSelected = false;
    public ?string $activeBranchSlug = null;
    public ?RumahSakit $activeBranch = null;
    public array $messages = [];
    public string $inputMessage = '';

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
    }

    public function changeBranch(): void
    {
        $this->branchSelected = false;
        $this->activeBranch = null;
        $this->activeBranchSlug = null;
        $this->messages = [];
        $this->inputMessage = '';
    }

    private function sendToAi($text){
        $response = Http::post(env('N8N_URL', 'production'), [
            'chatInput' => $text,
            'branch'    => $this->activeBranch->slug,
            'sessionKey' => Str::random(10)
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

    public function sendMessage(): void
    {
        $text = trim(mb_substr($this->inputMessage, 0, 150));
        if (! $text || ! $this->branchSelected) return;
        $this->inputMessage = '';

        $this->addUserMessage($text);


        $respon = $this->sendToAi($text);
        $this->addBotMessage($respon);
    }

    public function sendQuick(string $text): void
    {
        if (! $this->branchSelected) return;
        $this->addUserMessage($text);
        $this->generateReply($text);
    }

    // protected function generateReply(string $text): void
    // {
    //     $lower = mb_strtolower($text);
    //     $matched = null;

    //     foreach ($this->responses as $keyword => $data) {
    //         if (str_contains($lower, $keyword)) {
    //             $matched = $data;
    //             break;
    //         }
    //     }

    //     if ($matched) { 
    //         // inject alamat for lokasi keyword
    //         if (str_contains($lower, 'lokasi') && $this->activeBranch) {
    //             array_unshift($matched['card'], [
    //                 'icon' => 'ti-map-pin',
    //                 'text' => $this->activeBranch->alamat,
    //             ]);
    //         }
    //         // inject nomor IGD untuk info igd
    //         if (str_contains($lower, 'igd') && $this->activeBranch && $this->activeBranch->no_emergency !== '-') {
    //             $matched['card'][] = ['icon' => 'ti-phone', 'text' => 'IGD: ' . $this->activeBranch->no_emergency];
    //         }

    //         $this->addBotMessage(
    //             'Berikut informasi yang Anda butuhkan:',
    //             $matched['card'],
    //             $matched['opts']
    //         );
    //     } else {
    //         $this->addBotMessage(
    //             'Untuk informasi lebih lanjut, silakan hubungi kami langsung.',
    //             [
    //                 ['icon' => 'ti-phone', 'text' => 'Hotline: ' . ($this->activeBranch->no_hotline !== '-' ? $this->activeBranch->no_hotline : 'Belum tersedia')],
    //                 ['icon' => 'ti-ambulance', 'text' => 'IGD: ' . ($this->activeBranch->no_emergency !== '-' ? $this->activeBranch->no_emergency : 'Datang langsung')],
    //                 ['icon' => 'ti-clock-24', 'text' => 'Layanan 24 jam'],
    //             ],
    //             ['Lihat semua layanan', 'Jadwal dokter', 'Fasilitas']
    //         );
    //     }
    // }

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