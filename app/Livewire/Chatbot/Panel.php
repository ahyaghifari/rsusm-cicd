<?php

namespace App\Livewire\Chatbot;

use Livewire\Component;
use App\Models\RumahSakit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Panel extends Component
{
    private const SESSION_KEY  = 'chatbot_state';
    private const MAX_MESSAGES = 100;

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

    protected function getWelcomeMessage($branch){
            "Halo! Selamat datang di <strong>{$branch->nama}</strong>.<br>
            Saya dapat membantu anda untuk memberikan informasi mengenai : <br>
             - 🧑🏻‍⚕️ Dokter Kami, Spesialis, dan Jadwal Prakteknya <br>
             - 🛏️ Rawat Inap kami dan fasilitasnya <br>
             - 🩺 Rawat Jalan dan Poli <br>
             - ⭐ Fasilitas Kami (Unggulan, Pendukung, dan Penunjang Medis)
             - 📢 Promo
             - 🏥 Profil dan Partner Kami
             - 📞 Kontak Kami <br>
            <br>Ada yang bisa saya bantu hari ini?";
    }

    public function selectBranch(string $slug): void
    {
        $branch = RumahSakit::where('slug', $slug)->where('aktif', true)->first();

        if (! $branch) return;

        $this->activeBranch = $branch;
        $this->activeBranchSlug = $slug;
        $this->branchSelected = true;
        $this->messages = [];

        $this->addBotMessage($branch);

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

    public function resetConversation(): void
    {
        if (count($this->messages) < 10) return;

        $this->messages   = [];
        $this->sessionKey = '';
        $this->saveState();
    }

    private function sendToAi(string $text): string
    {
        if (empty($this->sessionKey)) {
            $this->sessionKey = Str::uuid()->toString();
        }

        $response = Http::timeout(60)->post(env('N8N_URL', 'http://127.0.0.1:5678/webhook/beb22058-f89c-4b21-9a8e-683583b10d5d'), [
            'chatInput'  => $text,
            'branch'     => $this->activeBranch->slug,
            'sessionKey' => $this->sessionKey,
        ]);

        if ($response->successful()) {
            return $response->json('output') ?? 'Maaf, tidak ada respons dari asisten.';
        }

        return 'Maaf, terjadi gangguan. Silakan coba lagi.';
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

    protected function addBotMessage(string $text): void
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