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

    // ── Rate limit AI (ubah angka di sini sesuai kebutuhan/kuota) ─────────────
    private const AI_BURST_LIMIT   = 10;   // maks. pesan AI dalam jendela singkat
    private const AI_BURST_MINUTES = 10;  // panjang jendela singkat (menit), reset otomatis
    private const AI_DAILY_LIMIT   = 100;  // maks. pesan AI per hari
    private const AI_DAILY_HOURS   = 24;  // panjang jendela harian (jam), reset otomatis

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
             - 🛏️ Rawat Inap kami dan fasilitasnya <br>
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
     * Cek batas pemakaian AI (burst & harian). Mengembalikan pesan ramah kalau
     * batas tercapai (null kalau masih boleh lanjut). Key digabung IP + session
     * supaya tidak mudah di-reset hanya dengan menghapus cookie.
     */
    private function aiRateLimited(): ?string
    {
        $id       = request()?->ip() . '|' . session()->getId();
        $burstKey = "chatbot-ai-burst:{$id}";
        $dailyKey = "chatbot-ai-daily:{$id}";

        if (RateLimiter::tooManyAttempts($burstKey, self::AI_BURST_LIMIT)) {
            return 'Anda mengirim pesan terlalu cepat. Silakan tunggu beberapa menit, lalu coba lagi.';
        }

        if (RateLimiter::tooManyAttempts($dailyKey, self::AI_DAILY_LIMIT)) {
            return 'Anda sudah mencapai batas tanya-jawab untuk hari ini. Silakan coba lagi besok, atau gunakan opsi kontak di bawah.';
        }

        RateLimiter::hit($burstKey, self::AI_BURST_MINUTES * 60);
        RateLimiter::hit($dailyKey, self::AI_DAILY_HOURS * 3600);

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
        $text = trim(mb_substr($text, 0, 150));
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