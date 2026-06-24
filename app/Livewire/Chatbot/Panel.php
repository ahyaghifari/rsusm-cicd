<?php

namespace App\Livewire\Chatbot;

use Livewire\Component;
use App\Models\Kontak;
use App\Models\RumahSakit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class Panel extends Component
{
    private const SESSION_KEY  = 'chatbot_state';
    private const MAX_MESSAGES = 50;

    // ── Rate limit AI (burst tetap hardcode, limit harian custom via .env) ───
    private const AI_BURST_LIMIT   = 10;   // maks. pesan AI dalam jendela singkat
    private const AI_BURST_MINUTES = 10;  // panjang jendela singkat (menit), reset otomatis

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
            return "Halo! Selamat datang di <strong>{$branch->nama}</strong>.<br>
            Saya dapat membantu anda untuk memberikan informasi mengenai : <br>
             - 🧑🏻‍⚕️ Dokter Kami, Spesialis, dan Jadwal Prakteknya <br>
             - 🛏️ Rawat Inap kami, fasilitasnya dan ketersediannya saat ini<br>
             - 🩺 Rawat Jalan dan Poli <br>
             - ⭐ Fasilitas Kami (Unggulan, Pendukung, dan Penunjang Medis)<br>
             - 📢 Promo<br>
             - 🏥 Profil dan Partner Kami<br>
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

        $this->addBotMessage($this->getWelcomeMessage($branch));

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

    /**
     * Cek batas pemakaian AI (burst & per-IP). Mengembalikan pesan ramah kalau
     * batas tercapai (null kalau masih boleh lanjut). Key burst tetap IP+session
     * (anti-spam jendela singkat), key kuota utama murni IP — sengaja dipilih
     * murni IP (bukan IP+session) supaya tidak bisa dilewati dengan menghapus
     * cookie/mode incognito. Konsekuensinya: pengunjung yang berbagi IP yang
     * sama (mis. WiFi ruang tunggu RS) berbagi kuota yang sama.
     */
    private function aiRateLimited(): ?string
    {
        $ip          = request()?->ip();
        $burstId     = $ip . '|' . session()->getId();
        $burstKey    = "chatbot-ai-burst:{$burstId}";
        $dailyKey    = "chatbot-ai-daily-ip:{$ip}";
        $dailyLimit  = (int) env('CHATBOT_AI_DAILY_LIMIT', 100);
        $dailyHours  = (int) env('CHATBOT_AI_DAILY_HOURS', 6);

        if (RateLimiter::tooManyAttempts($burstKey, self::AI_BURST_LIMIT)) {
            return 'Anda mengirim pesan terlalu cepat. Silakan tunggu beberapa menit, lalu coba lagi.';
        }

        if (RateLimiter::tooManyAttempts($dailyKey, $dailyLimit)) {
            return "Anda sudah mencapai batas tanya-jawab ({$dailyLimit} pertanyaan / {$dailyHours} jam). Silakan coba lagi nanti, atau gunakan opsi kontak di bawah.";
        }

        RateLimiter::hit($burstKey, self::AI_BURST_MINUTES * 60);
        RateLimiter::hit($dailyKey, $dailyHours * 3600);

        return null;
    }

    /**
     * @return array{text: string, failed: bool} 'failed' menandakan respons gagal/kosong
     *         (bukan batas rate limit) — dipakai UI untuk menampilkan opsi pemulihan.
     */
    private function sendToAi(string $text): array
    {
        if ($limitMessage = $this->aiRateLimited()) {
            return ['text' => $limitMessage, 'failed' => false];
        }

        if (empty($this->sessionKey)) {
            $this->sessionKey = Str::uuid()->toString();
        }

        $response = Http::timeout(60)->post(env('N8N_URL', 'http://127.0.0.1:5678/webhook/beb22058-f89c-4b21-9a8e-683583b10d5d'), [
            'chatInput'  => $text,
            'branch'     => $this->activeBranch->slug,
            'sessionKey' => $this->sessionKey,
        ]);

        if ($response->successful() && $response->json('output')) {
            return ['text' => $response->json('output'), 'failed' => false];
        }

        if ($response->successful()) {
            return ['text' => 'Maaf, tidak ada respons dari asisten.', 'failed' => true];
        }

        return ['text' => 'Maaf, terjadi gangguan. Silakan coba lagi.', 'failed' => true];
    }

    public function sendMessage(string $text = ''): void
    {
        // Teks bisa datang langsung sebagai parameter (dari Alpine) atau dari $inputMessage
        if ($text === '') {
            $text = $this->inputMessage;
        }
        $text = trim(mb_substr($text, 0, 100));
        if (! $text || ! $this->branchSelected) return;

        $this->inputMessage = '';

        $this->addUserMessage($text);

        $result = $this->sendToAi($text);
        $this->addBotMessage($result['text'], $result['failed'] ? $text : null);
        $this->saveState();
    }

    public function sendQuick(string $text): void
    {
        if (! $this->branchSelected) return;
        $this->addUserMessage($text);
        $result = $this->sendToAi($text);
        $this->addBotMessage($result['text'], $result['failed'] ? $text : null);
        $this->saveState();
    }

    /**
     * Kirim ulang pesan user sebelumnya — dipicu dari tombol "Kirim ulang pesan"
     * yang muncul di bawah balasan bot yang gagal.
     */
    public function retryLastMessage(string $text): void
    {
        if (! $this->branchSelected || ! trim($text)) return;

        $result = $this->sendToAi($text);
        $this->addBotMessage($result['text'], $result['failed'] ? $text : null);
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

    /**
     * @param string|null $retryText  Teks pesan user yang bisa dikirim ulang —
     *        diisi hanya saat balasan ini adalah hasil kegagalan (bukan rate limit).
     */
    protected function addBotMessage(string $text, ?string $retryText = null): void
    {
        $this->messages[] = [
            'type'   => 'bot',
            'text'   => $text,
            'time'   => now()->format('H:i'),
            'failed' => $retryText !== null,
            'retry'  => $retryText,
        ];
    }

    public function render()
    {
        $branches = RumahSakit::where('aktif', true)->get();

        $contacts = $this->activeBranch
            ? Kontak::where('rumah_sakit_id', $this->activeBranch->id)
                ->where('aktif', true)
                ->where('kategori', '!=', 'SOSIAL MEDIA')
                ->orderBy('sort_order')
                ->get(['label', 'value', 'kategori'])
            : collect();

        return view('rumah_sakit.chatbot.panel', compact('branches', 'contacts'));
    }
}